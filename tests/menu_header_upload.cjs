// Actual multipart uploads and production save helpers; persistence is JSON, no MySQL.
const {spawn} = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const root = path.resolve(__dirname,'..');
const temp = fs.mkdtempSync(path.join(os.tmpdir(),'sp-menu-upload-'));
const state = path.join(temp,'state.json');
const id1 = 1000000000 + Math.floor(Math.random()*100000000), id2 = id1+1;
const empty = {hero_tipo:'imagen',imagen_hero:null,hero_video_url:'',hero_video_archivo:null};
fs.writeFileSync(state, JSON.stringify({[id1]:empty,[id2]:empty}));
for (const id of [id1,id2]) if (fs.existsSync(path.join(root,'uploads','menus',String(id)))) throw Error('Carpeta de prueba ya existente');
const port = 21000+Math.floor(Math.random()*1000);
let server, checks=0;
const pause=ms=>new Promise(resolve=>setTimeout(resolve,ms));
const check=(ok,label)=>{if(!ok)throw Error(label);checks++;};
const jpg=fs.readFileSync(path.join(root,'assets/images/frontis_01.jpg'));
const png=fs.readFileSync(path.join(root,'assets/images/icono_ppt.png'));
const bigJpg=Buffer.concat([jpg,Buffer.alloc(3*1024*1024)]);
const physical=p=>path.join(root,...p.split('/'));
async function start(mode) {
  if(server) {server.kill();await pause(300);}
  // -c loads the same transport limits shipped for FPM; Fileinfo is enabled only for this process.
  const args=mode==='configured'?['-c',path.join(root,'.user.ini')]:['-n'];
  if(mode!=='no-fileinfo')args.push('-d','extension=fileinfo');
  args.push('-d','display_errors=0','-d',`error_log=${path.join(temp,'php.log')}`,
    '-S',`127.0.0.1:${port}`,'tests/menu_header_upload.php');
  server=spawn('php',args,{cwd:root,windowsHide:true,stdio:'ignore',env:{...process.env,SP_MENU_UPLOAD_TEST:'1',SP_MENU_UPLOAD_STATE:state}});
  for(let i=0;i<40;i++){try{if((await fetch(`http://127.0.0.1:${port}`)).ok)return;}catch{} await pause(150);}
  throw Error('Servidor PHP de prueba no disponible');
}
async function save(id,options={}) {
  const form=new FormData();
  for(const [key,value] of Object.entries({accion:'guardar_menu',id_menu:id,nombre:'Menu de prueba',estado:1,
    menu_hero_tipo:options.type||'imagen',...options.fields})) form.set(key,String(value));
  if(options.bytes) form.set(options.type==='video'?'menu_hero_video_archivo':'menu_imagen_hero',new Blob([options.bytes]),options.name||'imagen.jpg');
  return (await fetch(`http://127.0.0.1:${port}/?fail=${options.fail?1:0}`,{method:'POST',body:form})).json();
}
(async()=>{
  await start('default');
  let result=await save(id1,{bytes:bigJpg});
  check(!result.ok&&result.php_error===1,'Reproduccion UPLOAD_ERR_INI_SIZE con limite PHP 2M');
  check(result.rows[id1].imagen_hero===null,'PHP rechaza antes de modificar cabecera');
  await start('no-fileinfo');
  result=await save(id1,{bytes:jpg});
  check(!result.ok&&result.message.includes('Fileinfo'),'Fileinfo ausente no omite validacion MIME');
  await start('configured');
  result=await save(id1,{bytes:bigJpg,name:'Foto con espacios y acentos á.jpg'});
  check(result.ok,'JPG mayor de 2MB aceptado con configuracion corregida');
  const first=result.rows[id1].imagen_hero;
  check(first.startsWith(`uploads/menus/${id1}/`)&&fs.existsSync(physical(first)),'Carpeta automatica y ruta relativa');
  check(/^uploads\/menus\/\d+\/[a-z0-9-]+\.jpg$/.test(first),'Nombre seguro');
  check(result.heroes[id1].src==='/'+first,'Render hero desde menus');
  result=await save(id2,{bytes:png,name:'Otra imagen.png'});
  check(result.ok&&result.rows[id2].imagen_hero!==first,'PNG y dos menus independientes');
  const second=result.rows[id2].imagen_hero;
  result=await save(id1,{bytes:jpg,name:'Foto con espacios y acentos á.jpg'});
  const replacement=result.rows[id1].imagen_hero;
  check(result.ok&&replacement!==first&&!fs.existsSync(physical(first))&&fs.existsSync(physical(replacement)),'Reemplazo unico y borrado posterior');
  check(result.rows[id2].imagen_hero===second&&fs.existsSync(physical(second)),'Reemplazo no afecta otro menu');
  for(const invalid of [{bytes:Buffer.from('texto, no imagen'),name:'falsa.jpg'}, {bytes:jpg,name:'foto.php.jpg'},
    {bytes:Buffer.concat([jpg,Buffer.alloc(9*1024*1024)]),name:'grande.jpg'}]) {
    result=await save(id1,invalid); check(!result.ok&&result.rows[id1].imagen_hero===replacement&&fs.existsSync(physical(replacement)),'Subida invalida conserva anterior');
  }
  const directory=path.dirname(physical(replacement)); const before=fs.readdirSync(directory).length;
  result=await save(id1,{bytes:png,name:'rollback.png',fail:true});
  check(!result.ok&&result.rows[id1].imagen_hero===replacement&&fs.readdirSync(directory).length===before,'Rollback limpia solo nuevo archivo');
  check(!result.message.includes('privado'),'Error MySQL no revela informacion');
  result=await save(id1,{fields:{delete_menu_imagen_hero:1}});
  check(result.ok&&result.rows[id1].imagen_hero===null&&!fs.existsSync(physical(replacement)),'Eliminar imagen');
  check(result.heroes[id1].src==='/assets/images/frontis_01.jpg','Fallback explicito');
  result=await save(id1,{bytes:png,name:'regreso.png'}); check(result.ok,'Volver a subir');
  const image=result.rows[id1].imagen_hero;
  result=await save(id1,{type:'video',fields:{menu_hero_video_url:'https://www.youtube.com/watch?v=abcdefghi01'}});
  check(result.ok&&result.rows[id1].imagen_hero===null&&result.heroes[id1].type==='embed'&&!fs.existsSync(physical(image)),'Imagen a video URL');
  // Minimal MP4 container sufficient for MIME validation; playback is outside this upload test.
  const mp4=Buffer.from('000000186674797069736f6d0000020069736f6d69736f32','hex');
  result=await save(id1,{type:'video',bytes:mp4,name:'video.mp4'});
  check(result.ok&&result.heroes[id1].type==='video'&&result.rows[id1].hero_video_url==='','Video local mantiene prioridad');
  const video=result.rows[id1].hero_video_archivo;
  result=await save(id1,{bytes:jpg});
  check(result.ok&&result.rows[id1].hero_video_archivo===null&&result.rows[id1].hero_video_url===''&&!fs.existsSync(physical(video)),'Video a imagen');
  // Share a managed path in the fixture to exercise the real cross-record reference check.
  const rows=JSON.parse(fs.readFileSync(state)); const shared=rows[id1].imagen_hero; rows[id2].imagen_hero=shared;
  fs.writeFileSync(state,JSON.stringify(rows));
  result=await save(id1,{fields:{delete_menu_imagen_hero:1}});
  check(result.ok&&fs.existsSync(physical(shared))&&result.rows[id2].imagen_hero===shared,'Archivo compartido conservado');
  // Legacy paths lose their reference but are never removed by menu-header cleanup.
  const legacy=JSON.parse(fs.readFileSync(state)); legacy[id1].imagen_hero='uploads/submenus/archivo-legacy.jpg';
  fs.writeFileSync(state,JSON.stringify(legacy));
  result=await save(id1,{fields:{delete_menu_imagen_hero:1}});check(result.ok,'Referencia legacy eliminable sin borrar otro modulo');
  await start('default');
  const body='x'.repeat(9*1024*1024);
  result=await(await fetch(`http://127.0.0.1:${port}`,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body})).json();
  check(!result.ok&&result.message.includes('limite total'),'POST descartado detectado');
  const log=fs.readFileSync(path.join(temp,'php.log'),'utf8');
  check(log.includes('php_error=1')&&log.includes('image_size')&&log.includes('image_mime')&&log.includes('database_save'),'Diagnostico con causas distinguibles');
  console.log(JSON.stringify({checks,cause:'UPLOAD_ERR_INI_SIZE (2M)',uploads:'HTTP multipart reales',database:'simulada; no MySQL',limits:'150M/160M; imagenes 8MB en CMS'},null,2));
})().catch(error=>{console.error(error);process.exitCode=1;}).finally(async()=>{
  if(server){server.kill();await pause(300);}
  for(const id of [id1,id2]){
    const dir=path.resolve(root,'uploads','menus',String(id));
    if(!dir.startsWith(path.resolve(root,'uploads','menus')+path.sep))throw Error('Limpieza fuera de raiz');
    if(fs.existsSync(dir)){for(const item of fs.readdirSync(dir)){const file=path.join(dir,item);if(fs.statSync(file).isFile())fs.unlinkSync(file);}fs.rmdirSync(dir);}
  }
  for(const item of fs.readdirSync(temp))fs.unlinkSync(path.join(temp,item));fs.rmdirSync(temp);
});
