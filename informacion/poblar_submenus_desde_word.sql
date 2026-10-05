-- Población editorial desde NUEVA WEB COLEGIO SAN PABLO.docx
SET NAMES utf8mb4;
START TRANSACTION;

-- Verificación previa: el resultado debe ser 43.
SELECT COUNT(*) AS submenus_esperados FROM sub_menus WHERE id_sub_menu BETWEEN 1 AND 43;
-- Si el resultado anterior no es 43, ejecute ROLLBACK y revise el volcado antes de continuar.

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (1, 'Presentación', 'Somos una instituci&#243;n educativa cristiana de tradici&#243;n luterana, fundada en Montevideo en 1948 por la Congregaci&#243;n Evang&#233;lica Luterana San Pablo.', '<h3>NUESTRA IDENTIDAD</h3>
<h2>Qui&#233;nes somos</h2>
<p>Somos una instituci&#243;n educativa cristiana de tradici&#243;n luterana, fundada en Montevideo en 1948 por la Congregaci&#243;n Evang&#233;lica Luterana San Pablo.</p>
<p>A lo largo de m&#225;s de siete d&#233;cadas hemos acompa&#241;ado a generaciones de estudiantes, manteniendo vivo el compromiso de brindar una educaci&#243;n de calidad que combine conocimientos, valores y sentido de comunidad.</p>
<p>Actualmente ofrecemos una propuesta educativa que abarca desde la Primera Infancia hasta el Bachillerato, desarrollando experiencias de aprendizaje que integran el &#225;mbito acad&#233;mico, deportivo, art&#237;stico, tecnol&#243;gico y humano.</p>', 'Presentación | Colegio San Pablo', 'Somos una instituci&#243;n educativa cristiana de tradici&#243;n luterana, fundada en Montevideo en 1948 por la Congregaci&#243;n Evang&#233;lica Luterana San Pablo.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (2, 'Historia', 'La historia del Colegio San Pablo est&#225; profundamente vinculada a la historia de la comunidad luterana de Montevideo.', '<h3>NUESTRA HISTORIA</h3>
<h2>Una historia de educaci&#243;n, comunidad y compromiso</h2>
<p>La historia del Colegio San Pablo est&#225; profundamente vinculada a la historia de la comunidad luterana de Montevideo.</p>
<p>Impulsados por el deseo de ofrecer una educaci&#243;n basada en valores cristianos y una s&#243;lida formaci&#243;n acad&#233;mica, miembros de la Congregaci&#243;n Evang&#233;lica Luterana San Pablo fundaron el Colegio en 1948. Lo que comenz&#243; como una peque&#241;a escuela, nacida del esfuerzo y compromiso de familias, educadores y voluntarios, fue creciendo junto a la comunidad que le dio origen.</p>
<p>Desde sus primeros a&#241;os, el Colegio asumi&#243; el desaf&#237;o de formar ni&#241;os y j&#243;venes preparados para enfrentar los cambios de su tiempo, promoviendo una educaci&#243;n que integrara conocimientos, valores y desarrollo humano.</p>
<p>Con el paso de las d&#233;cadas, la instituci&#243;n ampli&#243; su propuesta educativa, incorporando nuevos niveles de ense&#241;anza, fortaleciendo sus programas acad&#233;micos y desarrollando espacios cada vez m&#225;s adecuados para acompa&#241;ar el crecimiento de sus estudiantes.</p>
<p>Como toda organizaci&#243;n con una larga trayectoria, tambi&#233;n enfrent&#243; momentos de dificultad y transformaci&#243;n. Gracias al compromiso de muchas personas que creyeron en el valor de este proyecto educativo, el Colegio logr&#243; superar desaf&#237;os importantes y construir nuevas oportunidades para su desarrollo.</p>
<p>Esa capacidad de renovaci&#243;n permiti&#243; consolidar una propuesta educativa propia, fiel a sus principios fundacionales y abierta a los desaf&#237;os del futuro.</p>
<p>En 2017 se cre&#243; la Fundaci&#243;n Educacional Concordia, organizaci&#243;n sin fines de lucro responsable de la gesti&#243;n institucional, con el prop&#243;sito de fortalecer la sostenibilidad, el crecimiento y la proyecci&#243;n de largo plazo del Colegio.</p>
<p>Hoy, m&#225;s de siete d&#233;cadas despu&#233;s de su fundaci&#243;n, el Colegio San Pablo es una comunidad educativa que acompa&#241;a a estudiantes desde la Primera Infancia hasta el Bachillerato, integrando excelencia acad&#233;mica, formaci&#243;n en valores, deporte, idiomas, innovaci&#243;n y una creciente proyecci&#243;n internacional.</p>
<p>Fieles a nuestra historia y comprometidos con las nuevas generaciones, continuamos construyendo una educaci&#243;n que inspire a aprender, crecer y servir.</p>
<p>Porque nuestra historia no se define solamente por lo que hemos logrado, sino tambi&#233;n por todo lo que a&#250;n estamos llamados a construir.</p>', 'Historia | Colegio San Pablo', 'La historia del Colegio San Pablo est&#225; profundamente vinculada a la historia de la comunidad luterana de Montevideo.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (3, 'Logotipo y lema', 'Entendemos la educaci&#243;n como un camino compartido. Aprender es crecer junto a otros, desarrollar talentos, construir conocimientos, fortalecer valores y prepararse para los desaf&#237;os de la vida.', '<h2>Aprender juntos para la vida</h2>
<p>Entendemos la educaci&#243;n como un camino compartido. Aprender es crecer junto a otros, desarrollar talentos, construir conocimientos, fortalecer valores y prepararse para los desaf&#237;os de la vida.</p>
<p>Desde hace d&#233;cadas acompa&#241;amos la formaci&#243;n de ni&#241;os y j&#243;venes en una comunidad educativa que integra excelencia acad&#233;mica, formaci&#243;n integral e inspiraci&#243;n cristiana, promoviendo el desarrollo de personas comprometidas consigo mismas, con los dem&#225;s y con la sociedad.</p>
<p>Nuestro prop&#243;sito es formar estudiantes capaces de pensar cr&#237;ticamente, actuar con responsabilidad, valorar la diversidad y contribuir positivamente al mundo que los rodea.</p>
<h3>Aprender juntos para la vida.</h3>
<p>Acompa&#241;amos a cada estudiante en la construcci&#243;n de aprendizajes significativos, promoviendo el desarrollo integral de sus capacidades, el compromiso con los dem&#225;s y la preparaci&#243;n para una vida plena y responsable.</p>', 'Logotipo y lema | Colegio San Pablo', 'Entendemos la educaci&#243;n como un camino compartido. Aprender es crecer junto a otros, desarrollar talentos, construir conocimientos, fortalecer valores y prepararse para los desaf&#237;os de la vida.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (4, 'Visión y misión', 'Ser una comunidad educativa de referencia que promueve el desarrollo integral de sus estudiantes como personas cr&#237;ticas, con s&#243;lida preparaci&#243;n acad&#233;mica y valores &#233;ticos, para servir a la sociedad.', '<h2>Nuestra misi&#243;n</h2>
<h2>Nuestra visi&#243;n</h2>
<p>Ser una comunidad educativa de referencia que promueve el desarrollo integral de sus estudiantes como personas cr&#237;ticas, con s&#243;lida preparaci&#243;n acad&#233;mica y valores &#233;ticos, para servir a la sociedad.</p>', 'Visión y misión | Colegio San Pablo', 'Ser una comunidad educativa de referencia que promueve el desarrollo integral de sus estudiantes como personas cr&#237;ticas, con s&#243;lida preparaci&#243;n acad&#233;mica y valores &#233;ticos, para servir a la sociedad.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (5, 'Principios de identidad', 'Respeto. Valorar la dignidad de cada persona, sus ideas y diferencias.', '<h2>Valores</h2>
<p>Respeto. Valorar la dignidad de cada persona, sus ideas y diferencias.</p>
<p>Solidaridad. Fomentar el apoyo mutuo y la colaboraci&#243;n en la comunidad escolar.</p>
<p>Esfuerzo. Promover la dedicaci&#243;n y la perseverancia en el aprendizaje y en la vida.</p>
<p>Inclusi&#243;n. Garantizar que todos los estudiantes tengan las mismas oportunidades.</p>
<p>Sostenibilidad. Integrar pr&#225;cticas y conciencia ambiental en el curr&#237;culo y la gesti&#243;n.</p>
<p>Valoraci&#243;n de la diversidad. Respetar y celebrar la pluralidad cultural, &#233;tnica y personal.</p>
<h3>COMUNIDAD SAN PABLO</h3>
<h2>Aprender, crecer y construir juntos</h2>
<p>El Colegio San Pablo es mucho m&#225;s que un lugar donde se desarrollan aprendizajes acad&#233;micos.</p>
<p>Somos una comunidad educativa formada por estudiantes, familias, educadores, colaboradores, exalumnos y amigos de la instituci&#243;n que comparten el compromiso de construir un entorno de aprendizaje, respeto y crecimiento mutuo.</p>
<p>Creemos que la educaci&#243;n alcanza su mayor significado cuando se vive en comunidad.</p>
<h2>Una comunidad que acompa&#241;a</h2>
<p>Cada estudiante es &#250;nico y cada familia tiene una historia propia.</p>
<p>Por eso promovemos relaciones cercanas, basadas en la confianza, el di&#225;logo y el acompa&#241;amiento permanente.</p>
<p>Buscamos que cada persona encuentre en el Colegio un espacio donde sentirse valorada, escuchada y parte de un proyecto compartido.</p>
<p>La cercan&#237;a humana constituye uno de los pilares fundamentales de nuestra identidad institucional.</p>
<h2>Familias que educan junto al Colegio</h2>
<p>Entendemos que la educaci&#243;n es una tarea compartida entre la familia y la instituci&#243;n.</p>
<p>Por ello promovemos una comunicaci&#243;n fluida y espacios de participaci&#243;n que fortalecen la colaboraci&#243;n y el acompa&#241;amiento de los procesos de aprendizaje y desarrollo de nuestros estudiantes.</p>
<p>La alianza entre familia y colegio contribuye a construir una experiencia educativa m&#225;s rica y significativa.</p>
<h2>Aprender a convivir</h2>
<p>La vida en comunidad ofrece oportunidades permanentes para aprender a respetar, dialogar, colaborar y construir acuerdos.</p>
<p>Promovemos una convivencia basada en la empat&#237;a, la responsabilidad y el reconocimiento de la dignidad de cada persona.</p>
<p>Creemos que los valores se fortalecen cuando se viven cotidianamente en las relaciones que construimos con los dem&#225;s.</p>
<h2>Una comunidad abierta y solidaria</h2>
<p>Nuestra vocaci&#243;n educativa se extiende m&#225;s all&#225; de los l&#237;mites del Colegio.</p>
<p>Participamos en proyectos, actividades y acciones que fortalecen el compromiso social, la solidaridad y el servicio a la comunidad.</p>
<p>Buscamos formar personas conscientes de su responsabilidad hacia los dem&#225;s y comprometidas con la construcci&#243;n de una sociedad m&#225;s justa, humana y fraterna.</p>
<h2>Una historia construida por muchas personas</h2>
<p>A lo largo de m&#225;s de siete d&#233;cadas, generaciones de estudiantes, familias, educadores y colaboradores han contribuido a construir la historia del Colegio San Pablo.</p>
<p>Ese legado contin&#250;a creciendo gracias al compromiso de quienes hoy forman parte de nuestra comunidad educativa y de quienes mantienen vivo el v&#237;nculo con la instituci&#243;n a trav&#233;s de los a&#241;os.</p>
<h2>Mirando juntos hacia el futuro</h2>
<p>Fieles a nuestra historia y comprometidos con los desaf&#237;os del presente, continuamos construyendo una comunidad educativa que aprende, crece y se proyecta hacia el futuro.</p>
<p>Una comunidad donde cada persona puede desarrollar sus talentos, aportar sus capacidades y encontrar oportunidades para aprender junto a otros.</p>
<p>Porque aprender juntos para la vida es tambi&#233;n construir juntos una comunidad que inspire, acompa&#241;e y transforme.</p>', 'Principios de identidad | Colegio San Pablo', 'Respeto. Valorar la dignidad de cada persona, sus ideas y diferencias.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (6, 'Propuesta pedagógica', 'Fieles a nuestra historia y comprometidos con los desaf&#237;os del presente, continuamos construyendo una comunidad educativa innovadora, abierta al mundo y centrada en las personas.', '<h2>Mirando hacia el futuro</h2>
<p>Fieles a nuestra historia y comprometidos con los desaf&#237;os del presente, continuamos construyendo una comunidad educativa innovadora, abierta al mundo y centrada en las personas.</p>
<p>A trav&#233;s de nuestro Plan de Desarrollo Institucional 2026-2030 impulsamos iniciativas orientadas a fortalecer la excelencia acad&#233;mica, la formaci&#243;n integral, la innovaci&#243;n pedag&#243;gica, la vida comunitaria y la sostenibilidad institucional.</p>
<p>Porque educar no consiste solamente en preparar para el ma&#241;ana, sino tambi&#233;n en aprender juntos para la vida.</p>
<h3>PROPUESTA EDUCATIVA</h3>
<h2>Aprender en cada etapa</h2>
<p>Cada momento del crecimiento presenta oportunidades y desaf&#237;os diferentes. Nuestra propuesta educativa se adapta a las necesidades de cada etapa, ofreciendo experiencias apropiadas para la edad, los intereses y el desarrollo de cada estudiante.</p>
<h2>NUESTROS PILARES EDUCATIVOS</h2>
<h3>Excelencia acad&#233;mica</h3>
<p>Promovemos aprendizajes profundos y significativos, desarrollando el pensamiento cr&#237;tico, la capacidad de an&#225;lisis y el compromiso con la mejora continua.</p>
<h3>Formaci&#243;n integral</h3>
<p>Educamos considerando todas las dimensiones de la persona: intelectual, emocional, social, f&#237;sica, &#233;tica y espiritual.</p>
<h3>Innovaci&#243;n pedag&#243;gica</h3>
<p>Incorporamos metodolog&#237;as activas, recursos tecnol&#243;gicos y propuestas interdisciplinarias que favorecen la participaci&#243;n y el protagonismo de los estudiantes.</p>
<h3>Comunidad y convivencia</h3>
<p>Fomentamos relaciones basadas en el respeto, la empat&#237;a, la responsabilidad y la colaboraci&#243;n, fortaleciendo el sentido de pertenencia y el compromiso con los dem&#225;s.</p>
<h3>Proyecci&#243;n internacional</h3>
<p>Impulsamos el aprendizaje de idiomas y experiencias que permiten comprender distintas culturas y prepararse para desenvolverse en un mundo global.</p>
<h2>APRENDER DENTRO Y FUERA DEL AULA</h2>
<p>La experiencia educativa se enriquece a trav&#233;s de m&#250;ltiples oportunidades de participaci&#243;n y crecimiento.</p>
<p>Idiomas</p>
<p>Deportes</p>
<p>Tecnolog&#237;a y Rob&#243;tica</p>
<p>Arte y Cultura</p>
<p>Vida con valores</p>
<p>Campamentos y salidas educativas</p>
<p>Proyectos solidarios</p>
<p>Viajes e intercambios internacionales</p>
<p>Cada una de estas experiencias contribuye al desarrollo de conocimientos, habilidades y valores que acompa&#241;ar&#225;n a nuestros estudiantes a lo largo de toda su vida.</p>
<h2>UN CAMINO COMPARTIDO</h2>
<p>Creemos que educar es una tarea que involucra a estudiantes, familias y educadores.</p>
<p>Por eso promovemos una relaci&#243;n cercana y de confianza con las familias, construyendo juntos una comunidad educativa comprometida con el bienestar, el aprendizaje y el crecimiento de cada estudiante.</p>
<p>Porque aprender juntos es tambi&#233;n crecer juntos para la vida.</p>
<h3>EXPERIENCIA SAN PABLO</h3>
<h2>Aprender m&#225;s all&#225; del aula</h2>
<p>La educaci&#243;n no ocurre &#250;nicamente dentro de una clase.</p>
<p>Cada experiencia, desaf&#237;o, encuentro y proyecto contribuye a la formaci&#243;n de nuestros estudiantes, ayud&#225;ndolos a descubrir talentos, desarrollar habilidades y construir v&#237;nculos significativos.</p>
<p>En el Colegio San Pablo entendemos que la formaci&#243;n integral se construye a trav&#233;s de m&#250;ltiples oportunidades de aprendizaje que complementan y enriquecen la propuesta acad&#233;mica.</p>
<p>Por eso promovemos experiencias que favorecen el desarrollo intelectual, f&#237;sico, emocional, social y espiritual de nuestros estudiantes.</p>
<h2>Formar personas para la vida</h2>
<p>Cada una de estas experiencias forma parte de un mismo prop&#243;sito: acompa&#241;ar a nuestros estudiantes en el desarrollo de conocimientos, habilidades, valores y actitudes que les permitan construir una vida plena, responsable y comprometida con su entorno.</p>
<p>Porque aprender juntos para la vida tambi&#233;n significa crecer, descubrir, compartir y transformar el mundo junto a otros.</p>
<h2>Comenzar aprendiendo para la vida</h2>
<p>En Primera Infancia e Inicial sembramos las bases que acompa&#241;ar&#225;n a nuestros estudiantes durante todo su recorrido educativo.</p>
<p>Porque aprender a convivir, descubrir, crear, expresarse y confiar en uno mismo son aprendizajes que permanecen para toda la vida.</p>', 'Propuesta pedagógica | Colegio San Pablo', 'Fieles a nuestra historia y comprometidos con los desaf&#237;os del presente, continuamos construyendo una comunidad educativa innovadora, abierta al mundo y centrada en las personas.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (7, 'Perfil del alumno', 'Entendemos la educaci&#243;n como un proceso integral que acompa&#241;a a cada estudiante en el desarrollo de sus capacidades, talentos y proyectos personales.', '<h2>Formamos personas para la vida</h2>
<p>Entendemos la educaci&#243;n como un proceso integral que acompa&#241;a a cada estudiante en el desarrollo de sus capacidades, talentos y proyectos personales.</p>
<p>Nuestra propuesta educativa combina excelencia acad&#233;mica, formaci&#243;n en valores, desarrollo socioemocional, actividad f&#237;sica, expresi&#243;n art&#237;stica, tecnolog&#237;a y una mirada internacional, preparando a ni&#241;os y j&#243;venes para afrontar los desaf&#237;os de un mundo en constante transformaci&#243;n.</p>
<p>Creemos que aprender implica mucho m&#225;s que adquirir conocimientos. Significa desarrollar el pensamiento cr&#237;tico, la creatividad, la autonom&#237;a, la capacidad de trabajar con otros y el compromiso con la comunidad.</p>
<p>Por eso acompa&#241;amos a nuestros estudiantes desde sus primeros a&#241;os hasta el ingreso a la educaci&#243;n superior, promoviendo experiencias de aprendizaje significativas que contribuyan a su desarrollo integral.</p>', 'Perfil del alumno | Colegio San Pablo', 'Entendemos la educaci&#243;n como un proceso integral que acompa&#241;a a cada estudiante en el desarrollo de sus capacidades, talentos y proyectos personales.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (8, 'Estructura física', 'Creemos que los espacios tambi&#233;n educan.', '<h3>CAMPUS SAN PABLO</h3>
<h2>Espacios que inspiran aprendizaje, encuentro y crecimiento</h2>
<p>Creemos que los espacios tambi&#233;n educan.</p>
<p>Cada aula, laboratorio, espacio deportivo, &#225;rea verde y lugar de encuentro forma parte de una experiencia educativa pensada para favorecer el aprendizaje, la convivencia y el desarrollo integral de nuestros estudiantes.</p>
<p>Por eso trabajamos permanentemente en el crecimiento y la mejora de nuestra infraestructura, creando entornos que acompa&#241;en las necesidades de una educaci&#243;n din&#225;mica, innovadora y centrada en las personas.</p>
<p>Nuestro campus combina tradici&#243;n, naturaleza, deporte, tecnolog&#237;a y espacios especialmente dise&#241;ados para aprender, compartir y crecer.</p>
<h2>Un lugar para cada etapa</h2>
<p>Nuestros estudiantes cuentan con espacios adaptados a las caracter&#237;sticas y necesidades de cada momento de su desarrollo.</p>
<p>Desde la Primera Infancia hasta el Bachillerato, cada ambiente est&#225; pensado para favorecer el aprendizaje, la exploraci&#243;n, la creatividad y el bienestar.</p>
<p>La organizaci&#243;n de los espacios acompa&#241;a el crecimiento progresivo de nuestros estudiantes y contribuye a generar experiencias educativas significativas.</p>
<h2>Aprender dentro y fuera del aula</h2>
<p>Entendemos que el aprendizaje ocurre en m&#250;ltiples contextos.</p>
<p>Por eso nuestros espacios favorecen tanto el trabajo acad&#233;mico como la interacci&#243;n, el movimiento, la experimentaci&#243;n y el encuentro con otros.</p>
<p>Aulas especializadas, laboratorios, bibliotecas, espacios tecnol&#243;gicos, &#225;reas deportivas y entornos al aire libre ampl&#237;an las oportunidades de aprendizaje y enriquecen la experiencia educativa.</p>
<h2>Naturaleza y bienestar</h2>
<p>Los espacios abiertos y las &#225;reas verdes forman parte de la vida cotidiana de nuestra comunidad educativa.</p>
<p>Estos entornos favorecen el juego, la actividad f&#237;sica, la convivencia y el contacto con la naturaleza, contribuyendo al bienestar f&#237;sico y emocional de nuestros estudiantes.</p>
<p>Creemos que aprender tambi&#233;n implica disfrutar, explorar y descubrir el mundo que nos rodea.</p>
<h2>Un campus en permanente crecimiento</h2>
<p>Fieles a nuestro compromiso con la mejora continua, impulsamos proyectos que fortalecen y ampl&#237;an las oportunidades educativas que ofrecemos a nuestros estudiantes y familias.</p>
<p>Las inversiones en infraestructura reflejan una visi&#243;n de largo plazo orientada a crear espacios modernos, funcionales y preparados para responder a los desaf&#237;os de la educaci&#243;n del futuro.</p>
<p>Cada mejora representa una nueva oportunidad para aprender, crecer y construir comunidad.</p>
<h2>Tradici&#243;n y futuro</h2>
<p>Nuestro campus refleja la historia de una instituci&#243;n con m&#225;s de siete d&#233;cadas de trayectoria y, al mismo tiempo, la mirada innovadora con la que proyectamos el futuro.</p>
<p>La convivencia entre espacios hist&#243;ricos y nuevas instalaciones simboliza el compromiso de preservar nuestra identidad mientras continuamos evolucionando para responder a las necesidades de las nuevas generaciones.</p>
<h2>Un lugar para aprender juntos para la vida</h2>
<p>M&#225;s que un conjunto de edificios, el Campus San Pablo es un espacio donde se construyen aprendizajes, amistades, experiencias y recuerdos que acompa&#241;an a nuestros estudiantes durante toda su vida.</p>
<p>Un lugar donde cada rinc&#243;n invita a descubrir, compartir, crecer y proyectarse hacia el futuro.</p>
<p>Porque los espacios tambi&#233;n forman parte de la educaci&#243;n y contribuyen a construir una experiencia que trasciende las aulas.</p>
<p>Im&#225;genes:</p>
<h3>Primera Infancia e Inicial</h3>
<p>Un entorno especialmente dise&#241;ado para los m&#225;s peque&#241;os.</p>
<h3>Sede Central</h3>
<p>El coraz&#243;n de la vida acad&#233;mica y comunitaria.</p>
<h3>Nuevo Gimnasio</h3>
<p>Deporte, encuentro y formaci&#243;n integral.</p>
<h3>Espacios Deportivos</h3>
<p>Espacios para aprender, competir y crecer.</p>
<h3>Castillo San Pablo</h3>
<p>Patrimonio, historia y proyecci&#243;n institucional.</p>
<h3>&#193;reas Verdes y Espacios de Encuentro</h3>
<p>Naturaleza, convivencia y bienestar.</p>', 'Estructura física | Colegio San Pablo', 'Creemos que los espacios tambi&#233;n educan.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (9, 'Administración', 'Somos gestionados por la Fundaci&#243;n Educacional Concordia, una organizaci&#243;n sin fines de lucro creada con el prop&#243;sito de garantizar la sostenibilidad, el crecimiento y la proyecci&#243;n de la instituci&#243;n.', '<h2>Fundaci&#243;n Educacional Concordia</h2>
<p>Somos gestionados por la Fundaci&#243;n Educacional Concordia, una organizaci&#243;n sin fines de lucro creada con el prop&#243;sito de garantizar la sostenibilidad, el crecimiento y la proyecci&#243;n de la instituci&#243;n.</p>
<p>Todos los recursos generados son reinvertidos en el desarrollo educativo, la mejora de la infraestructura, la innovaci&#243;n pedag&#243;gica y el fortalecimiento de los servicios que ofrece el Colegio.</p>
<p>La Fundaci&#243;n cuenta con un Consejo de Administraci&#243;n integrado por profesionales con amplia experiencia en educaci&#243;n, gesti&#243;n, derecho y actividad empresarial. Asimismo, mantiene una valiosa vinculaci&#243;n con referentes internacionales que aportan conocimiento, experiencia y perspectivas que enriquecen el desarrollo estrat&#233;gico de la instituci&#243;n. Esta diversidad de perspectivas aporta una visi&#243;n estrat&#233;gica e internacional que contribuye al desarrollo permanente de la instituci&#243;n.</p>
<h3>FUNDACI&#211;N EDUCACIONAL CONCORDIA</h3>
<h2>Una instituci&#243;n al servicio de la educaci&#243;n</h2>
<p>La Fundaci&#243;n Educacional Concordia es una organizaci&#243;n sin fines de lucro responsable de la gesti&#243;n y el desarrollo institucional del Colegio San Pablo.</p>
<p>Su prop&#243;sito es asegurar la sostenibilidad, el crecimiento y la proyecci&#243;n de largo plazo de la instituci&#243;n, garantizando que cada decisi&#243;n contribuya al cumplimiento de nuestra misi&#243;n educativa.</p>
<p>Inspirada por los valores que dieron origen al Colegio, la Fundaci&#243;n trabaja para fortalecer una educaci&#243;n de calidad, accesible, innovadora y comprometida con la formaci&#243;n integral de las personas.</p>
<h2>Educaci&#243;n con visi&#243;n de futuro</h2>
<p>La Fundaci&#243;n fue creada con el objetivo de consolidar un modelo de gesti&#243;n que permita proyectar el desarrollo institucional m&#225;s all&#225; de las personas y las circunstancias de cada momento.</p>
<p>Su labor se orienta a generar las condiciones necesarias para que el Colegio contin&#250;e creciendo, innovando y respondiendo a los desaf&#237;os de una sociedad en permanente transformaci&#243;n.</p>
<p>De esta manera, contribuye a asegurar la continuidad y el fortalecimiento del proyecto educativo para las futuras generaciones.</p>
<h2>Una organizaci&#243;n sin fines de lucro</h2>
<p>Como instituci&#243;n sin fines de lucro, la totalidad de los recursos generados se reinvierte en el desarrollo educativo y el fortalecimiento institucional.</p>
<p>Las inversiones realizadas se destinan a la mejora de la infraestructura, la innovaci&#243;n pedag&#243;gica, la formaci&#243;n de los equipos de trabajo, el desarrollo de nuevos proyectos y la ampliaci&#243;n de oportunidades para los estudiantes.</p>
<p>Esta forma de gesti&#243;n refleja el compromiso de colocar la educaci&#243;n en el centro de cada decisi&#243;n.</p>
<h2>Gobernanza y compromiso institucional</h2>
<p>La Fundaci&#243;n cuenta con una estructura de gobernanza orientada a promover la responsabilidad, la transparencia y la sostenibilidad institucional.</p>
<p>Su Consejo de Administraci&#243;n est&#225; integrado por profesionales con experiencia en educaci&#243;n, gesti&#243;n, derecho y actividad empresarial, comprometidos con el desarrollo del proyecto educativo y con los valores que inspiran la instituci&#243;n.</p>
<p>La diversidad de experiencias y conocimientos contribuye a enriquecer la reflexi&#243;n estrat&#233;gica y fortalecer la toma de decisiones.</p>
<h2>Vinculaci&#243;n con referentes internacionales</h2>
<p>La Fundaci&#243;n mantiene v&#237;nculos con referentes del &#225;mbito educativo, acad&#233;mico y empresarial de distintos pa&#237;ses, generando oportunidades de intercambio, aprendizaje y cooperaci&#243;n.</p>
<p>Estas relaciones permiten incorporar nuevas perspectivas, compartir buenas pr&#225;cticas y enriquecer el desarrollo institucional a partir de experiencias relevantes en contextos diversos.</p>
<p>La apertura al di&#225;logo y al aprendizaje permanente forma parte de nuestra manera de entender la educaci&#243;n y el crecimiento institucional.</p>
<h2>Comprometidos con nuestra misi&#243;n</h2>
<p>Cada proyecto, inversi&#243;n y decisi&#243;n impulsada por la Fundaci&#243;n tiene un prop&#243;sito com&#250;n: fortalecer la misi&#243;n educativa del Colegio San Pablo y contribuir a la formaci&#243;n de personas preparadas para aprender, crecer y servir a la sociedad.</p>
<p>Porque creemos que construir una instituci&#243;n s&#243;lida es una forma de asegurar que las futuras generaciones contin&#250;en encontrando oportunidades para aprender juntos para la vida.</p>', 'Administración | Colegio San Pablo', 'Somos gestionados por la Fundaci&#243;n Educacional Concordia, una organizaci&#243;n sin fines de lucro creada con el prop&#243;sito de garantizar la sostenibilidad, el crecimiento y la proyecci&#243;n de la instituci&#243;n.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (10, 'Presentación', 'Promovemos el aprendizaje a trav&#233;s del juego, la exploraci&#243;n, la creatividad y el descubrimiento. En un entorno seguro y afectivo, acompa&#241;amos los primeros pasos en la construcci&#243;n de la autonom&#237;a, la convivencia y el desarrollo de habilidades fundamentales para la vida.', '<h3>Primera Infancia e Inicial</h3>
<p>Promovemos el aprendizaje a trav&#233;s del juego, la exploraci&#243;n, la creatividad y el descubrimiento. En un entorno seguro y afectivo, acompa&#241;amos los primeros pasos en la construcci&#243;n de la autonom&#237;a, la convivencia y el desarrollo de habilidades fundamentales para la vida.</p>
<h3>PRIMERA INFANCIA E INICIAL</h3>
<h2>Los primeros pasos de un gran camino</h2>
<p>Los primeros a&#241;os de vida son fundamentales para el desarrollo de cada ni&#241;o. Acompa&#241;amos esta etapa con una propuesta educativa que combina afecto, juego, descubrimiento y aprendizaje, favoreciendo el crecimiento integral en un entorno seguro, estimulante y lleno de oportunidades.</p>
<p>Creemos que cada ni&#241;o aprende a su propio ritmo y posee talentos &#250;nicos. Por eso ofrecemos experiencias que promueven la curiosidad, la creatividad, la autonom&#237;a y la confianza necesarias para construir una base s&#243;lida para toda la vida.</p>', 'Presentación | Colegio San Pablo', 'Promovemos el aprendizaje a trav&#233;s del juego, la exploraci&#243;n, la creatividad y el descubrimiento. En un entorno seguro y afectivo, acompa&#241;amos los primeros pasos en la construcci&#243;n de la autonom&#237;a, la convivencia y el desarrollo de habilidades fundamentales para la vida.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (12, 'Propuesta bilingüe', 'Desde los primeros a&#241;os, los ni&#241;os tienen contacto cotidiano con el idioma ingl&#233;s a trav&#233;s de propuestas especialmente dise&#241;adas para su etapa de desarrollo.', '<h3>Creciendo en un entorno biling&#252;e</h3>
<p>Desde los primeros a&#241;os, los ni&#241;os tienen contacto cotidiano con el idioma ingl&#233;s a trav&#233;s de propuestas especialmente dise&#241;adas para su etapa de desarrollo.</p>
<p>Mediante juegos, canciones, cuentos, rutinas y experiencias significativas, incorporan el idioma de manera natural, favoreciendo la comprensi&#243;n, la comunicaci&#243;n y la confianza para expresarse en una segunda lengua.</p>
<p>Esta exposici&#243;n temprana contribuye al desarrollo de competencias ling&#252;&#237;sticas que se fortalecen progresivamente a lo largo de todo el recorrido educativo.</p>', 'Propuesta bilingüe | Colegio San Pablo', 'Desde los primeros a&#241;os, los ni&#241;os tienen contacto cotidiano con el idioma ingl&#233;s a trav&#233;s de propuestas especialmente dise&#241;adas para su etapa de desarrollo.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (13, 'Actividades', 'La educaci&#243;n en esta etapa contempla todas las dimensiones del crecimiento infantil.', '<h2>Un desarrollo integral</h2>
<p>La educaci&#243;n en esta etapa contempla todas las dimensiones del crecimiento infantil.</p>
<p>Promovemos experiencias vinculadas a:</p>
<p>Lenguaje y comunicaci&#243;n.</p>
<p>Pensamiento l&#243;gico y matem&#225;tico.</p>
<p>Expresi&#243;n art&#237;stica y creatividad.</p>
<p>Educaci&#243;n f&#237;sica y desarrollo corporal.</p>
<p>Educaci&#243;n cristiana y formaci&#243;n en valores.</p>
<p>Tecnolog&#237;a educativa.</p>
<p>Ingl&#233;s.</p>
<p>Conocimiento del entorno natural y social.</p>
<p>De esta manera, los ni&#241;os desarrollan habilidades y competencias que los preparan para los desaf&#237;os de las siguientes etapas educativas.</p>', 'Actividades | Colegio San Pablo', 'La educaci&#243;n en esta etapa contempla todas las dimensiones del crecimiento infantil.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (14, 'Presentación', 'A trav&#233;s del juego, la exploraci&#243;n y la interacci&#243;n con otros, los ni&#241;os desarrollan habilidades fundamentales para su crecimiento personal, social y acad&#233;mico.', '<h2>Un espacio para descubrir el mundo</h2>
<p>A trav&#233;s del juego, la exploraci&#243;n y la interacci&#243;n con otros, los ni&#241;os desarrollan habilidades fundamentales para su crecimiento personal, social y acad&#233;mico.</p>
<p>Nuestra propuesta favorece:</p>
<p>El desarrollo de la autonom&#237;a.</p>
<p>La construcci&#243;n de v&#237;nculos saludables.</p>
<p>La expresi&#243;n de emociones e ideas.</p>
<p>La comunicaci&#243;n y el lenguaje.</p>
<p>El desarrollo de la motricidad.</p>
<p>La curiosidad y el deseo de aprender.</p>
<p>La convivencia y el respeto por los dem&#225;s.</p>
<p>Cada experiencia est&#225; dise&#241;ada para que los ni&#241;os aprendan de forma significativa, disfrutando del proceso y fortaleciendo su confianza.</p>
<h2>Un entorno pensado para crecer</h2>
<p>Nuestros espacios est&#225;n dise&#241;ados especialmente para responder a las necesidades de los m&#225;s peque&#241;os, ofreciendo ambientes seguros, estimulantes y adecuados para el juego, el aprendizaje y la convivencia.</p>
<p>Cada rinc&#243;n invita a explorar, crear, descubrir y disfrutar de nuevas experiencias en un clima de cuidado y acompa&#241;amiento.</p>', 'Presentación | Colegio San Pablo', 'A trav&#233;s del juego, la exploraci&#243;n y la interacci&#243;n con otros, los ni&#241;os desarrollan habilidades fundamentales para su crecimiento personal, social y acad&#233;mico.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (15, 'Propuesta curricular', 'El juego constituye una herramienta fundamental para el aprendizaje durante la Primera Infancia y la Educaci&#243;n Inicial.', '<h2>Aprender jugando</h2>
<p>El juego constituye una herramienta fundamental para el aprendizaje durante la Primera Infancia y la Educaci&#243;n Inicial.</p>
<p>A trav&#233;s de propuestas l&#250;dicas, proyectos, experiencias sensoriales y actividades de exploraci&#243;n, los ni&#241;os construyen conocimientos, desarrollan habilidades y descubren nuevas formas de comprender el mundo que los rodea.</p>
<p>Nuestro equipo docente acompa&#241;a estos procesos con cercan&#237;a, sensibilidad y profesionalismo, respetando las caracter&#237;sticas y necesidades de cada etapa.</p>', 'Propuesta curricular | Colegio San Pablo', 'El juego constituye una herramienta fundamental para el aprendizaje durante la Primera Infancia y la Educaci&#243;n Inicial.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (16, 'Propuesta bilingüe', 'Desde los primeros a&#241;os, los ni&#241;os tienen contacto cotidiano con el idioma ingl&#233;s a trav&#233;s de propuestas especialmente dise&#241;adas para su etapa de desarrollo.', '<h3>Creciendo en un entorno biling&#252;e</h3>
<p>Desde los primeros a&#241;os, los ni&#241;os tienen contacto cotidiano con el idioma ingl&#233;s a trav&#233;s de propuestas especialmente dise&#241;adas para su etapa de desarrollo.</p>
<p>Mediante juegos, canciones, cuentos, rutinas y experiencias significativas, incorporan el idioma de manera natural, favoreciendo la comprensi&#243;n, la comunicaci&#243;n y la confianza para expresarse en una segunda lengua.</p>
<p>Esta exposici&#243;n temprana contribuye al desarrollo de competencias ling&#252;&#237;sticas que se fortalecen progresivamente a lo largo de todo el recorrido educativo.</p>', 'Propuesta bilingüe | Colegio San Pablo', 'Desde los primeros a&#241;os, los ni&#241;os tienen contacto cotidiano con el idioma ingl&#233;s a trav&#233;s de propuestas especialmente dise&#241;adas para su etapa de desarrollo.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (17, 'Actividades', 'Entendemos que la familia cumple un papel fundamental en el desarrollo de los ni&#241;os.', '<h2>Aprender en comunidad</h2>
<p>Entendemos que la familia cumple un papel fundamental en el desarrollo de los ni&#241;os.</p>
<p>Por eso promovemos una comunicaci&#243;n cercana y permanente, construyendo una relaci&#243;n de confianza que nos permite acompa&#241;ar juntos cada proceso de crecimiento, aprendizaje y bienestar.</p>
<p>La educaci&#243;n es una tarea compartida, y creemos que el trabajo conjunto entre familia y colegio enriquece significativamente la experiencia educativa.</p>', 'Actividades | Colegio San Pablo', 'Entendemos que la familia cumple un papel fundamental en el desarrollo de los ni&#241;os.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (18, 'Presentación', 'Fortalecemos los aprendizajes fundamentales y estimulamos la curiosidad, la creatividad y el gusto por aprender. Promovemos el desarrollo acad&#233;mico junto con la formaci&#243;n en valores, el trabajo colaborativo y la participaci&#243;n activa de los estudiantes.', '<h3>Primaria</h3>
<p>Fortalecemos los aprendizajes fundamentales y estimulamos la curiosidad, la creatividad y el gusto por aprender. Promovemos el desarrollo acad&#233;mico junto con la formaci&#243;n en valores, el trabajo colaborativo y la participaci&#243;n activa de los estudiantes.</p>
<h3>PRIMARIA</h3>
<h2>Aprender con curiosidad, crecer con confianza</h2>
<p>La Educaci&#243;n Primaria es una etapa fundamental en la construcci&#243;n de conocimientos, habilidades y valores que acompa&#241;ar&#225;n a los estudiantes durante toda su trayectoria educativa.</p>
<p>Promovemos aprendizajes significativos que despiertan la curiosidad, fortalecen la autonom&#237;a y desarrollan la confianza necesaria para enfrentar nuevos desaf&#237;os.</p>
<p>A trav&#233;s de experiencias variadas y motivadoras, acompa&#241;amos a cada estudiante en el descubrimiento de sus capacidades, favoreciendo el desarrollo acad&#233;mico, personal y social en un ambiente de respeto, acompa&#241;amiento y entusiasmo por aprender.</p>
<h2>Aprender a convivir</h2>
<p>La convivencia constituye un aspecto central de la formaci&#243;n integral.</p>
<p>Promovemos relaciones basadas en el respeto, la empat&#237;a, la responsabilidad y la colaboraci&#243;n, favoreciendo la construcci&#243;n de v&#237;nculos saludables y el desarrollo de habilidades sociales que acompa&#241;ar&#225;n a los estudiantes dentro y fuera del &#225;mbito escolar.</p>
<p>Entendemos que aprender a convivir es tan importante como aprender contenidos acad&#233;micos.</p>
<h2>Familia y colegio: un camino compartido</h2>
<p>Creemos que la educaci&#243;n alcanza su m&#225;ximo potencial cuando existe una alianza s&#243;lida entre familia y colegio.</p>
<p>Por eso mantenemos una comunicaci&#243;n cercana y permanente, acompa&#241;ando a las familias en cada etapa del crecimiento y fortaleciendo juntos el desarrollo integral de los estudiantes.</p>', 'Presentación | Colegio San Pablo', 'Fortalecemos los aprendizajes fundamentales y estimulamos la curiosidad, la creatividad y el gusto por aprender. Promovemos el desarrollo acad&#233;mico junto con la formaci&#243;n en valores, el trabajo colaborativo y la participaci&#243;n activa de los estudiantes.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (19, 'Propuesta curricular', 'Creemos que aprender implica comprender, explorar, preguntar, crear y aplicar conocimientos en diferentes contextos.', '<h2>Una educaci&#243;n que despierta el deseo de aprender</h2>
<p>Creemos que aprender implica comprender, explorar, preguntar, crear y aplicar conocimientos en diferentes contextos.</p>
<p>Por eso promovemos propuestas que estimulan el pensamiento cr&#237;tico, la resoluci&#243;n de problemas, la creatividad y el trabajo colaborativo, ayudando a los estudiantes a construir aprendizajes s&#243;lidos y duraderos.</p>
<p>Nuestro objetivo es que cada ni&#241;o desarrolle herramientas para aprender de forma aut&#243;noma, asumir responsabilidades y participar activamente en su proceso educativo.</p>
<h2>Preparando el camino para nuevos desaf&#237;os</h2>
<p>Durante la Educaci&#243;n Primaria, los estudiantes desarrollan conocimientos, habilidades y valores que constituyen la base para afrontar con seguridad y confianza las etapas posteriores de su formaci&#243;n.</p>
<p>A trav&#233;s de una propuesta que integra excelencia acad&#233;mica, formaci&#243;n integral y acompa&#241;amiento cercano, buscamos que cada estudiante descubra el placer de aprender y construya las herramientas necesarias para seguir creciendo a lo largo de toda la vida.</p>', 'Propuesta curricular | Colegio San Pablo', 'Creemos que aprender implica comprender, explorar, preguntar, crear y aplicar conocimientos en diferentes contextos.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (20, 'Propuesta bilingüe', 'El aprendizaje de idiomas forma parte esencial de nuestra propuesta educativa.', '<h2>Una propuesta biling&#252;e que abre puertas al mundo</h2>
<p>El aprendizaje de idiomas forma parte esencial de nuestra propuesta educativa.</p>
<p>Desde Primaria, los estudiantes desarrollan progresivamente sus competencias en ingl&#233;s a trav&#233;s de experiencias de aprendizaje dise&#241;adas para fortalecer la comprensi&#243;n, la comunicaci&#243;n y el uso pr&#225;ctico del idioma en diferentes contextos.</p>
<p>La propuesta biling&#252;e favorece el desarrollo de habilidades ling&#252;&#237;sticas y culturales que ampl&#237;an horizontes y preparan a los estudiantes para desenvolverse en un mundo cada vez m&#225;s conectado.</p>
<h2>Idiomas</h2>
<p>Promovemos el aprendizaje de idiomas como una herramienta para comprender el mundo, comunicarse con diferentes culturas y ampliar horizontes personales y acad&#233;micos.</p>
<p>Nuestra propuesta acompa&#241;a a los estudiantes desde los primeros a&#241;os hasta la obtenci&#243;n de certificaciones internacionales.</p>', 'Propuesta bilingüe | Colegio San Pablo', 'El aprendizaje de idiomas forma parte esencial de nuestra propuesta educativa.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (21, 'Actividades extracurriculares', 'La formaci&#243;n de nuestros estudiantes trasciende el aula.', '<h2>Desarrollo integral en cada experiencia</h2>
<p>La formaci&#243;n de nuestros estudiantes trasciende el aula.</p>
<p>Promovemos oportunidades de crecimiento a trav&#233;s de:</p>
<p>Educaci&#243;n f&#237;sica y deportes.</p>
<p>Arte y expresi&#243;n creativa.</p>
<p>Tecnolog&#237;a y pensamiento computacional.</p>
<p>Educaci&#243;n cristiana y formaci&#243;n en valores.</p>
<p>Proyectos interdisciplinarios.</p>
<p>Campamentos y salidas educativas.</p>
<p>Actividades culturales y recreativas.</p>
<p>Proyectos solidarios y de servicio.</p>
<p>Cada experiencia contribuye al desarrollo de habilidades, actitudes y valores fundamentales para la vida.</p>
<h2>Deportes</h2>
<p>El deporte constituye una herramienta educativa fundamental para el desarrollo de h&#225;bitos saludables, el trabajo en equipo, la disciplina, la perseverancia y el liderazgo.</p>
<p>A trav&#233;s de una amplia propuesta deportiva, nuestros estudiantes encuentran espacios para crecer, superarse y disfrutar de la actividad f&#237;sica.</p>
<h3>DEPORTES</h3>
<h2>Aprender tambi&#233;n es moverse, superarse y crecer junto a otros</h2>
<p>Entendemos el deporte como una herramienta educativa fundamental para el desarrollo integral de nuestros estudiantes.</p>
<p>A trav&#233;s de la actividad f&#237;sica, los ni&#241;os y j&#243;venes fortalecen h&#225;bitos saludables, desarrollan habilidades motrices, aprenden a trabajar en equipo y descubren valores que los acompa&#241;ar&#225;n durante toda la vida.</p>
<p>El deporte contribuye a formar personas perseverantes, responsables, respetuosas y comprometidas con el logro de objetivos individuales y colectivos.</p>
<p>Por eso ocupa un lugar destacado dentro de nuestra propuesta educativa.</p>
<h2>Una formaci&#243;n que va m&#225;s all&#225; de la competencia</h2>
<p>Creemos que el verdadero valor del deporte no se encuentra &#250;nicamente en los resultados, sino en todo lo que se aprende durante el proceso.</p>
<p>Cada entrenamiento, cada partido y cada desaf&#237;o representan oportunidades para desarrollar:</p>
<p>Disciplina y constancia.</p>
<p>Trabajo en equipo.</p>
<p>Liderazgo.</p>
<p>Respeto por los dem&#225;s.</p>
<p>Tolerancia a la frustraci&#243;n.</p>
<p>Capacidad de superaci&#243;n.</p>
<p>Compromiso y responsabilidad.</p>
<p>Estas experiencias contribuyen al crecimiento personal y fortalecen competencias fundamentales para la vida.</p>
<h2>Deporte para todos</h2>
<p>Promovemos la participaci&#243;n de estudiantes con diferentes intereses, habilidades y niveles de experiencia.</p>
<p>Nuestro objetivo es que cada estudiante encuentre un espacio donde disfrutar de la actividad f&#237;sica, desarrollar sus capacidades y construir v&#237;nculos positivos con sus compa&#241;eros.</p>
<p>La pr&#225;ctica deportiva constituye una oportunidad para aprender, compartir y crecer dentro de un ambiente de respeto, compa&#241;erismo y sana competencia.</p>
<h2>Una propuesta deportiva en crecimiento</h2>
<p>Durante los &#250;ltimos a&#241;os hemos fortalecido significativamente nuestra propuesta deportiva, incorporando nuevas disciplinas, ampliando oportunidades de participaci&#243;n y desarrollando espacios especialmente dise&#241;ados para la pr&#225;ctica y el aprendizaje.</p>
<p>La educaci&#243;n f&#237;sica y el deporte forman parte de una visi&#243;n institucional que entiende el bienestar f&#237;sico como un componente esencial del desarrollo integral.</p>
<h2>Instalaciones para aprender y disfrutar</h2>
<p>Contamos con espacios que favorecen el desarrollo de actividades deportivas y recreativas en diferentes etapas educativas.</p>
<p>Nuestros estudiantes disfrutan de instalaciones que promueven el movimiento, el encuentro y la pr&#225;ctica deportiva en un entorno seguro y estimulante.</p>
<p>El crecimiento de la infraestructura deportiva refleja nuestro compromiso permanente con la formaci&#243;n integral de ni&#241;os y j&#243;venes.</p>
<h2>Deporte y comunidad</h2>
<p>El deporte tambi&#233;n constituye una oportunidad para fortalecer el sentido de pertenencia y construir comunidad.</p>
<p>Las competencias, encuentros, actividades recreativas y proyectos deportivos generan experiencias compartidas que enriquecen la vida escolar y fortalecen los v&#237;nculos entre estudiantes, familias y educadores.</p>
<h2>Formar personas para la vida</h2>
<p>M&#225;s all&#225; de las canchas y los resultados, buscamos que cada estudiante descubra en el deporte una oportunidad para conocerse mejor, desarrollar su potencial y aprender valores que permanecer&#225;n a lo largo de toda su vida.</p>
<p>Porque cada desaf&#237;o superado, cada esfuerzo realizado y cada meta alcanzada representan aprendizajes que trascienden el &#225;mbito deportivo y contribuyen a la formaci&#243;n de personas &#237;ntegras y comprometidas con su entorno.</p>', 'Actividades extracurriculares | Colegio San Pablo', 'La formaci&#243;n de nuestros estudiantes trasciende el aula.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (23, 'Presentación', 'Acompa&#241;amos a los estudiantes en una etapa clave de crecimiento personal y acad&#233;mico. Favorecemos la construcci&#243;n de la autonom&#237;a, el pensamiento cr&#237;tico, la responsabilidad y la capacidad de enfrentar nuevos desaf&#237;os.', '<h3>Ciclo B&#225;sico</h3>
<p>Acompa&#241;amos a los estudiantes en una etapa clave de crecimiento personal y acad&#233;mico. Favorecemos la construcci&#243;n de la autonom&#237;a, el pensamiento cr&#237;tico, la responsabilidad y la capacidad de enfrentar nuevos desaf&#237;os.</p>
<h3>CICLO B&#193;SICO</h3>', 'Presentación | Colegio San Pablo', 'Acompa&#241;amos a los estudiantes en una etapa clave de crecimiento personal y acad&#233;mico. Favorecemos la construcci&#243;n de la autonom&#237;a, el pensamiento cr&#237;tico, la responsabilidad y la capacidad de enfrentar nuevos desaf&#237;os.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (24, 'Propuesta curricular', 'La etapa de Ciclo B&#225;sico representa un momento de grandes cambios y oportunidades. Los estudiantes comienzan a desarrollar una mayor autonom&#237;a, fortalecen su identidad y ampl&#237;an su comprensi&#243;n del mundo que los rodea.', '<h2>Crecer, pensar y construir el propio camino</h2>
<p>La etapa de Ciclo B&#225;sico representa un momento de grandes cambios y oportunidades. Los estudiantes comienzan a desarrollar una mayor autonom&#237;a, fortalecen su identidad y ampl&#237;an su comprensi&#243;n del mundo que los rodea.</p>
<p>En el Colegio San Pablo acompa&#241;amos este proceso a trav&#233;s de una propuesta educativa que combina excelencia acad&#233;mica, formaci&#243;n integral y acompa&#241;amiento cercano, ayudando a cada estudiante a descubrir sus fortalezas, asumir nuevos desaf&#237;os y construir confianza en s&#237; mismo.</p>
<p>Creemos que esta etapa no consiste solamente en aprender m&#225;s contenidos, sino tambi&#233;n en aprender a pensar, reflexionar, convivir y tomar decisiones responsables.</p>
<h2>Aprender a pensar cr&#237;ticamente</h2>
<p>Promovemos experiencias de aprendizaje que desaf&#237;an a los estudiantes a analizar, investigar, argumentar y construir conocimiento de manera activa.</p>
<p>Nuestro enfoque busca desarrollar habilidades que les permitan comprender la realidad, resolver problemas, formular preguntas relevantes y participar de forma reflexiva en los distintos &#225;mbitos de la vida.</p>
<p>Acompa&#241;amos a los estudiantes para que se conviertan en protagonistas de su propio aprendizaje, fortaleciendo progresivamente su autonom&#237;a y responsabilidad.</p>
<h2>Innovaci&#243;n y aprendizaje para el siglo XXI</h2>
<p>La tecnolog&#237;a constituye una herramienta fundamental para aprender, crear y resolver desaf&#237;os.</p>
<p>Promovemos el desarrollo de habilidades vinculadas al pensamiento computacional, la programaci&#243;n, la rob&#243;tica, la investigaci&#243;n y el uso responsable de los recursos digitales.</p>
<p>Buscamos que los estudiantes comprendan la tecnolog&#237;a como una herramienta para la creatividad, la innovaci&#243;n y la construcci&#243;n de soluciones.</p>
<h2>Tecnolog&#237;a y Rob&#243;tica</h2>
<p>Incorporamos la tecnolog&#237;a como una herramienta para aprender, crear, investigar e innovar.</p>
<p>A trav&#233;s de propuestas vinculadas a la programaci&#243;n, la rob&#243;tica y el pensamiento computacional, los estudiantes desarrollan habilidades esenciales para el siglo XXI.</p>
<h3>TECNOLOG&#205;A Y ROB&#211;TICA</h3>
<h2>Crear, innovar y construir soluciones para el futuro</h2>
<p>La tecnolog&#237;a forma parte de la vida cotidiana y transforma permanentemente la forma en que aprendemos, trabajamos y nos relacionamos.</p>
<p>Entendemos que educar para el futuro implica mucho m&#225;s que ense&#241;ar a utilizar herramientas digitales. Significa desarrollar la capacidad de pensar, crear, investigar, resolver problemas y adaptarse a nuevos desaf&#237;os.</p>
<p>Por eso promovemos experiencias de aprendizaje que integran tecnolog&#237;a, programaci&#243;n, rob&#243;tica y pensamiento computacional como herramientas para la construcci&#243;n de conocimientos y el desarrollo de competencias para el siglo XXI.</p>
<h2>Aprender haciendo</h2>
<p>Creemos que los estudiantes aprenden mejor cuando tienen la oportunidad de experimentar, explorar y construir soluciones por s&#237; mismos.</p>
<p>A trav&#233;s de proyectos, desaf&#237;os y experiencias pr&#225;cticas, los estudiantes desarrollan habilidades que les permiten comprender c&#243;mo funcionan las tecnolog&#237;as que utilizan y c&#243;mo pueden aplicarlas para resolver problemas reales.</p>
<p>La curiosidad, la creatividad y la capacidad de innovaci&#243;n ocupan un lugar central dentro de este proceso.</p>
<h2>Pensamiento computacional</h2>
<p>Promovemos el desarrollo del pensamiento computacional como una herramienta para analizar situaciones, identificar patrones, organizar informaci&#243;n y construir soluciones de manera l&#243;gica y eficiente.</p>
<p>Estas habilidades fortalecen la capacidad de razonamiento, la toma de decisiones y la resoluci&#243;n de problemas, contribuyendo al desarrollo acad&#233;mico en m&#250;ltiples &#225;reas del conocimiento.</p>
<h2>Programaci&#243;n y rob&#243;tica</h2>
<p>La programaci&#243;n y la rob&#243;tica ofrecen oportunidades &#250;nicas para transformar ideas en proyectos concretos.</p>
<p>A trav&#233;s de experiencias adecuadas para cada etapa educativa, los estudiantes desarrollan competencias vinculadas al dise&#241;o, la planificaci&#243;n, la creatividad y el trabajo colaborativo.</p>
<p>Cada proyecto representa una oportunidad para aprender a experimentar, corregir errores, perseverar y construir nuevas soluciones.</p>
<h2>Tecnolog&#237;a al servicio del aprendizaje</h2>
<p>La incorporaci&#243;n de recursos tecnol&#243;gicos en el aula permite enriquecer las experiencias educativas y ampliar las posibilidades de aprendizaje.</p>
<p>Utilizamos la tecnolog&#237;a como una herramienta que favorece la investigaci&#243;n, la colaboraci&#243;n, la comunicaci&#243;n y la creaci&#243;n de contenidos, promoviendo un uso responsable, cr&#237;tico y consciente de los entornos digitales.</p>
<p>Nuestro objetivo no es simplemente ense&#241;ar tecnolog&#237;a, sino ense&#241;ar a utilizarla con prop&#243;sito y sentido.</p>
<h2>Ciudadan&#237;a digital</h2>
<p>Preparar a los estudiantes para el futuro tambi&#233;n implica acompa&#241;arlos en la construcci&#243;n de h&#225;bitos responsables dentro del entorno digital.</p>
<p>Promovemos el desarrollo de competencias vinculadas al uso &#233;tico de la tecnolog&#237;a, el cuidado de la informaci&#243;n, la convivencia digital y la comprensi&#243;n de los desaf&#237;os que plantea la sociedad conectada en la que vivimos.</p>
<p>Creemos que la educaci&#243;n tecnol&#243;gica debe estar acompa&#241;ada por una s&#243;lida formaci&#243;n humana y val&#243;rica.</p>
<h2>Preparados para los desaf&#237;os del ma&#241;ana</h2>
<p>Vivimos en una &#233;poca caracterizada por cambios acelerados y nuevas oportunidades.</p>
<p>Por ello buscamos que nuestros estudiantes desarrollen la capacidad de aprender continuamente, adaptarse a nuevas realidades y participar activamente en la construcci&#243;n del futuro.</p>
<p>La tecnolog&#237;a y la rob&#243;tica constituyen herramientas valiosas dentro de este proceso, pero el verdadero objetivo es formar personas creativas, cr&#237;ticas, responsables y capaces de transformar ideas en acciones.</p>
<p>Porque educar para el futuro significa ayudar a nuestros estudiantes a construirlo.</p>', 'Propuesta curricular | Colegio San Pablo', 'La etapa de Ciclo B&#225;sico representa un momento de grandes cambios y oportunidades. Los estudiantes comienzan a desarrollar una mayor autonom&#237;a, fortalecen su identidad y ampl&#237;an su comprensi&#243;n del mundo que los rodea.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (25, 'Propuesta bilingüe', 'El aprendizaje de idiomas contin&#250;a ocupando un lugar destacado dentro de nuestra propuesta educativa.', '<h2>Una educaci&#243;n conectada con el mundo</h2>
<p>El aprendizaje de idiomas contin&#250;a ocupando un lugar destacado dentro de nuestra propuesta educativa.</p>
<p>A trav&#233;s del desarrollo de competencias comunicativas en ingl&#233;s y portugu&#233;s, los estudiantes ampl&#237;an sus posibilidades de interacci&#243;n con diferentes culturas y realidades, prepar&#225;ndose para participar activamente en un contexto global.</p>
<p>La incorporaci&#243;n de experiencias internacionales, proyectos interdisciplinarios y actividades de intercambio cultural enriquecen esta formaci&#243;n y contribuyen a desarrollar una mirada amplia y abierta al mundo.</p>
<h3>IDIOMAS</h3>
<h2>Aprender idiomas, abrirse al mundo</h2>
<p>Entendemos los idiomas como una herramienta para comunicarse, comprender otras culturas y ampliar las oportunidades personales, acad&#233;micas y profesionales.</p>
<p>Por eso el aprendizaje de lenguas extranjeras forma parte de nuestra propuesta educativa desde los primeros a&#241;os, acompa&#241;ando el desarrollo de nuestros estudiantes a lo largo de toda su trayectoria escolar.</p>
<p>Aprender idiomas significa descubrir nuevas formas de pensar, relacionarse y comprender el mundo que nos rodea.</p>
<h2>Una propuesta que comienza desde la infancia</h2>
<p>El contacto con el ingl&#233;s se inicia desde los primeros a&#241;os a trav&#233;s de experiencias especialmente dise&#241;adas para cada etapa del desarrollo.</p>
<p>Mediante juegos, canciones, cuentos, proyectos y actividades significativas, los estudiantes incorporan progresivamente el idioma de forma natural, desarrollando confianza y habilidades comunicativas desde edades tempranas.</p>
<p>Esta experiencia temprana constituye la base para un aprendizaje s&#243;lido y progresivo en las etapas posteriores.</p>
<h2>Crecer en un entorno biling&#252;e</h2>
<p>A medida que avanzan en su trayectoria educativa, los estudiantes fortalecen sus competencias ling&#252;&#237;sticas mediante propuestas que favorecen la comprensi&#243;n, la comunicaci&#243;n oral, la lectura y la producci&#243;n escrita.</p>
<p>El uso del idioma en contextos significativos permite desarrollar habilidades que trascienden el &#225;mbito acad&#233;mico y preparan a los estudiantes para interactuar en un mundo cada vez m&#225;s conectado.</p>
<p>Nuestra propuesta promueve el aprendizaje activo y la utilizaci&#243;n pr&#225;ctica de la lengua en situaciones reales de comunicaci&#243;n.</p>
<h2>Ingl&#233;s y portugu&#233;s para una formaci&#243;n global</h2>
<p>Adem&#225;s del ingl&#233;s, los estudiantes tienen la oportunidad de desarrollar competencias en portugu&#233;s, ampliando sus posibilidades de comunicaci&#243;n e integraci&#243;n en un contexto regional e internacional.</p>
<p>El aprendizaje de m&#250;ltiples idiomas favorece la flexibilidad cognitiva, la comprensi&#243;n intercultural y la capacidad de desenvolverse en entornos diversos.</p>
<h2>Certificaciones internacionales</h2>
<p>Acompa&#241;amos a nuestros estudiantes en la preparaci&#243;n para certificaciones internacionales que validan sus competencias ling&#252;&#237;sticas y les permiten proyectarse hacia nuevos desaf&#237;os acad&#233;micos y profesionales.</p>
<p>Estas certificaciones constituyen una valiosa herramienta para continuar estudios superiores y participar en experiencias educativas internacionales.</p>
<h2>Idiomas y experiencias internacionales</h2>
<p>El aprendizaje de idiomas se fortalece a trav&#233;s de experiencias que conectan a los estudiantes con diferentes culturas y realidades.</p>
<p>Proyectos interculturales, actividades internacionales, intercambios y viajes educativos enriquecen este proceso, permitiendo aplicar los conocimientos adquiridos en contextos aut&#233;nticos y significativos.</p>
<p>De esta manera, los idiomas se convierten en una puerta de acceso al mundo.</p>
<h2>Preparados para un mundo sin fronteras</h2>
<p>Vivimos en una sociedad global donde la capacidad de comunicarse en diferentes idiomas constituye una herramienta fundamental para el aprendizaje, el trabajo y la construcci&#243;n de relaciones.</p>
<p>Por eso buscamos que nuestros estudiantes desarrollen no solo competencias ling&#252;&#237;sticas, sino tambi&#233;n una mirada abierta, respetuosa y curiosa hacia otras culturas y formas de vida.</p>
<p>Porque aprender idiomas es tambi&#233;n aprender a comprender el mundo y encontrar nuevas formas de participar en &#233;l.</p>', 'Propuesta bilingüe | Colegio San Pablo', 'El aprendizaje de idiomas contin&#250;a ocupando un lugar destacado dentro de nuestra propuesta educativa.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (26, 'Actividades extracurriculares', 'La experiencia educativa se complementa con m&#250;ltiples oportunidades de crecimiento personal y social.', '<h2>Formaci&#243;n integral dentro y fuera del aula</h2>
<p>La experiencia educativa se complementa con m&#250;ltiples oportunidades de crecimiento personal y social.</p>
<p>Nuestros estudiantes participan en propuestas relacionadas con:</p>
<p>Deportes y actividad f&#237;sica.</p>
<p>Arte y expresi&#243;n cultural.</p>
<p>Tecnolog&#237;a y rob&#243;tica.</p>
<p>Educaci&#243;n cristiana y formaci&#243;n en valores.</p>
<p>Campamentos y salidas educativas.</p>
<p>Proyectos solidarios y de servicio.</p>
<p>Actividades de liderazgo y participaci&#243;n estudiantil.</p>
<p>Experiencias de intercambio cultural e internacional.</p>
<p>Estas instancias fortalecen habilidades fundamentales para la vida, favoreciendo el desarrollo de la responsabilidad, la colaboraci&#243;n y el compromiso con los dem&#225;s.</p>
<h2>Viajes e Intercambios</h2>
<p>Las experiencias educativas fuera del aula ampl&#237;an horizontes, enriquecen aprendizajes y favorecen el encuentro con nuevas culturas y realidades.</p>
<p>A trav&#233;s de viajes educativos, intercambios y actividades internacionales, los estudiantes desarrollan una mirada abierta al mundo y fortalecen competencias para desenvolverse en contextos diversos.</p>
<h3>VIAJES E INTERCAMBIOS</h3>
<h2>Aprender del mundo para comprender mejor nuestro lugar en &#233;l</h2>
<p>La educaci&#243;n trasciende los l&#237;mites del aula.</p>
<p>Cada encuentro con nuevas culturas, realidades y formas de pensar representa una oportunidad &#250;nica para aprender, crecer y desarrollar una mirada m&#225;s amplia sobre el mundo.</p>
<p>Por eso promovemos experiencias educativas nacionales e internacionales que enriquecen la formaci&#243;n acad&#233;mica y humana de nuestros estudiantes, ayud&#225;ndolos a desarrollar autonom&#237;a, sensibilidad cultural y una comprensi&#243;n m&#225;s profunda de la sociedad en la que viven.</p>
<p>Viajar es descubrir. Pero tambi&#233;n es aprender, reflexionar y transformarse.</p>
<h2>Experiencias que ampl&#237;an horizontes</h2>
<p>Los viajes educativos permiten que los estudiantes vivan experiencias de aprendizaje significativas en contextos reales.</p>
<p>A trav&#233;s del contacto directo con diferentes culturas, idiomas, tradiciones y formas de vida, los estudiantes fortalecen competencias que dif&#237;cilmente podr&#237;an desarrollarse &#250;nicamente dentro del aula.</p>
<p>Cada experiencia contribuye a desarrollar:</p>
<p>Curiosidad intelectual.</p>
<p>Adaptabilidad.</p>
<p>Autonom&#237;a.</p>
<p>Capacidad de comunicaci&#243;n.</p>
<p>Sensibilidad intercultural.</p>
<p>Pensamiento global.</p>
<p>Confianza personal.</p>
<p>Trabajo colaborativo.</p>
<h2>Aprender a trav&#233;s de la experiencia</h2>
<p>Las actividades y programas de intercambio complementan los aprendizajes desarrollados en las distintas &#225;reas curriculares.</p>
<p>El encuentro con nuevas realidades favorece la comprensi&#243;n de procesos hist&#243;ricos, sociales, culturales y econ&#243;micos, permitiendo conectar conocimientos acad&#233;micos con experiencias concretas y significativas.</p>
<p>De esta manera, el aprendizaje se vuelve m&#225;s profundo, relevante y duradero.</p>
<h2>Idiomas que cobran vida</h2>
<p>Las experiencias internacionales ofrecen oportunidades valiosas para utilizar los idiomas en contextos aut&#233;nticos de comunicaci&#243;n.</p>
<p>El contacto con personas de diferentes pa&#237;ses y culturas permite fortalecer habilidades ling&#252;&#237;sticas, desarrollar confianza y comprender la importancia de la comunicaci&#243;n como puente entre distintas realidades.</p>
<p>Los idiomas dejan de ser &#250;nicamente una asignatura para convertirse en una herramienta real de interacci&#243;n y aprendizaje.</p>
<h2>Construir ciudadan&#237;a global</h2>
<p>Vivimos en un mundo cada vez m&#225;s interconectado.</p>
<p>Por ello buscamos que nuestros estudiantes desarrollen una mirada abierta, respetuosa y responsable hacia otras culturas, aprendiendo a valorar la diversidad y a comprender los desaf&#237;os compartidos por las sociedades contempor&#225;neas.</p>
<p>Las experiencias internacionales contribuyen a formar ciudadanos capaces de participar activamente en un mundo diverso y en permanente transformaci&#243;n.</p>
<h2>Una educaci&#243;n conectada con el mundo</h2>
<p>A lo largo de su trayectoria educativa, nuestros estudiantes tienen la posibilidad de participar en diferentes propuestas de intercambio, viajes educativos y actividades internacionales que complementan su formaci&#243;n acad&#233;mica y personal.</p>
<p>Estas experiencias forman parte de una visi&#243;n educativa que busca preparar a los j&#243;venes para desenvolverse con confianza, responsabilidad y apertura en contextos locales e internacionales.</p>
<h2>Aprendizajes que permanecen</h2>
<p>Los recuerdos de un viaje pueden durar toda la vida.</p>
<p>Pero m&#225;s importante a&#250;n son los aprendizajes que surgen de cada experiencia: la capacidad de adaptarse, convivir con personas diferentes, resolver desaf&#237;os, comunicarse, trabajar en equipo y comprender otras realidades.</p>
<p>Porque viajar no consiste &#250;nicamente en conocer nuevos lugares, sino tambi&#233;n en descubrir nuevas formas de mirar el mundo y de comprendernos a nosotros mismos.</p>
<p>Y esos aprendizajes acompa&#241;an a nuestros estudiantes mucho m&#225;s all&#225; del regreso a casa.</p>', 'Actividades extracurriculares | Colegio San Pablo', 'La experiencia educativa se complementa con m&#250;ltiples oportunidades de crecimiento personal y social.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (27, 'Servicios educativos', 'Entendemos que esta etapa presenta desaf&#237;os acad&#233;micos, emocionales y sociales que requieren acompa&#241;amiento y orientaci&#243;n.', '<h2>Acompa&#241;ar la adolescencia</h2>
<p>Entendemos que esta etapa presenta desaf&#237;os acad&#233;micos, emocionales y sociales que requieren acompa&#241;amiento y orientaci&#243;n.</p>
<p>Por eso promovemos una cultura institucional basada en la cercan&#237;a, el respeto y el di&#225;logo, ofreciendo espacios de escucha y apoyo que favorecen el bienestar y el desarrollo integral de cada estudiante.</p>
<p>Nuestro objetivo es que cada joven se sienta acompa&#241;ado, valorado y capaz de construir su propio proyecto personal.</p>
<h2>Construyendo el futuro</h2>
<p>Durante el Ciclo B&#225;sico los estudiantes desarrollan conocimientos, habilidades y valores que les permitir&#225;n afrontar con confianza los desaf&#237;os del Bachillerato y de las etapas posteriores de su formaci&#243;n.</p>
<p>A trav&#233;s de una propuesta educativa que integra exigencia acad&#233;mica, innovaci&#243;n pedag&#243;gica y formaci&#243;n humana, buscamos formar j&#243;venes cr&#237;ticos, responsables y comprometidos con su entorno.</p>
<p>Porque aprender tambi&#233;n significa descubrir qui&#233;nes somos y c&#243;mo queremos contribuir al mundo.</p>', 'Servicios educativos | Colegio San Pablo', 'Entendemos que esta etapa presenta desaf&#237;os acad&#233;micos, emocionales y sociales que requieren acompa&#241;amiento y orientaci&#243;n.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (28, 'Presentación', 'Brindamos una s&#243;lida preparaci&#243;n acad&#233;mica orientada a la continuidad de estudios superiores y a la construcci&#243;n del proyecto de vida de cada estudiante. Promovemos la reflexi&#243;n, la toma de decisiones responsables y el desarrollo de competencias para el mundo actual.', '<h3>Bachillerato</h3>
<p>Brindamos una s&#243;lida preparaci&#243;n acad&#233;mica orientada a la continuidad de estudios superiores y a la construcci&#243;n del proyecto de vida de cada estudiante. Promovemos la reflexi&#243;n, la toma de decisiones responsables y el desarrollo de competencias para el mundo actual.</p>
<h3>BACHILLERATO</h3>
<h2>Proyectar el futuro con confianza</h2>
<p>El Bachillerato representa una etapa decisiva en la formaci&#243;n de los j&#243;venes. Es el momento de profundizar conocimientos, desarrollar una mayor autonom&#237;a y comenzar a construir proyectos personales, acad&#233;micos y profesionales.</p>
<p>Acompa&#241;amos este proceso a trav&#233;s de una propuesta educativa que combina exigencia acad&#233;mica, pensamiento cr&#237;tico, formaci&#243;n integral y orientaci&#243;n para la vida.</p>
<p>Nuestro objetivo es que cada estudiante pueda descubrir sus intereses, fortalecer sus capacidades y prepararse para afrontar con confianza los desaf&#237;os de la educaci&#243;n superior, el mundo laboral y la participaci&#243;n activa en la sociedad.</p>', 'Presentación | Colegio San Pablo', 'Brindamos una s&#243;lida preparaci&#243;n acad&#233;mica orientada a la continuidad de estudios superiores y a la construcci&#243;n del proyecto de vida de cada estudiante. Promovemos la reflexi&#243;n, la toma de decisiones responsables y el desarrollo de competencias para el mundo actual.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (29, 'Propuesta curricular', 'Promovemos aprendizajes rigurosos y significativos que permiten a los estudiantes desarrollar conocimientos s&#243;lidos y competencias necesarias para continuar su formaci&#243;n.', '<h2>Excelencia acad&#233;mica para nuevos desaf&#237;os</h2>
<p>Promovemos aprendizajes rigurosos y significativos que permiten a los estudiantes desarrollar conocimientos s&#243;lidos y competencias necesarias para continuar su formaci&#243;n.</p>
<p>A trav&#233;s de metodolog&#237;as activas, proyectos interdisciplinarios y experiencias de aprendizaje desafiantes, fomentamos la capacidad de an&#225;lisis, la argumentaci&#243;n, la investigaci&#243;n y la resoluci&#243;n de problemas.</p>
<p>Buscamos que cada estudiante asuma un rol protag&#243;nico en su aprendizaje y desarrolle las herramientas necesarias para aprender durante toda la vida.</p>
<h2>Preparaci&#243;n para la educaci&#243;n superior</h2>
<p>Nuestra propuesta acad&#233;mica est&#225; orientada a facilitar una transici&#243;n exitosa hacia los estudios terciarios y universitarios.</p>
<p>Acompa&#241;amos a los estudiantes en el fortalecimiento de h&#225;bitos de estudio, la organizaci&#243;n personal, la toma de decisiones y la construcci&#243;n de su proyecto acad&#233;mico y profesional.</p>
<p>El desarrollo de la autonom&#237;a intelectual y la capacidad de asumir nuevos desaf&#237;os constituyen aspectos centrales de esta etapa.</p>
<h2>Innovaci&#243;n, tecnolog&#237;a y pensamiento cr&#237;tico</h2>
<p>Vivimos en una sociedad en permanente transformaci&#243;n.</p>
<p>Por ello promovemos el desarrollo de competencias vinculadas al uso responsable de la tecnolog&#237;a, la investigaci&#243;n, la creatividad, la innovaci&#243;n y el pensamiento cr&#237;tico.</p>
<p>Buscamos formar j&#243;venes capaces de comprender los cambios de su tiempo, adaptarse a nuevas realidades y contribuir activamente a la construcci&#243;n de soluciones para los desaf&#237;os del futuro.</p>', 'Propuesta curricular | Colegio San Pablo', 'Promovemos aprendizajes rigurosos y significativos que permiten a los estudiantes desarrollar conocimientos s&#243;lidos y competencias necesarias para continuar su formaci&#243;n.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (30, 'Propuesta bilingüe', 'La formaci&#243;n en idiomas contin&#250;a ocupando un lugar destacado dentro de nuestra propuesta educativa.', '<h2>Una mirada abierta al mundo</h2>
<p>La formaci&#243;n en idiomas contin&#250;a ocupando un lugar destacado dentro de nuestra propuesta educativa.</p>
<p>El fortalecimiento de las competencias en ingl&#233;s y portugu&#233;s, junto con experiencias internacionales, proyectos interculturales y oportunidades de intercambio, permite a los estudiantes ampliar horizontes y prepararse para desenvolverse en contextos diversos y globalizados.</p>
<p>Promovemos una educaci&#243;n que conecta el aprendizaje con las realidades y desaf&#237;os del mundo contempor&#225;neo.</p>
<h2>Preparados para el mundo que viene</h2>
<p>Al finalizar el Bachillerato, nuestros estudiantes cuentan con una s&#243;lida formaci&#243;n acad&#233;mica, una base &#233;tica y val&#243;rica consistente y las herramientas necesarias para continuar aprendiendo, adaptarse a nuevos contextos y afrontar con confianza los desaf&#237;os del futuro.</p>
<p>Porque educar no consiste solamente en preparar para los pr&#243;ximos ex&#225;menes, sino en formar personas capaces de construir su propio camino y aportar al bienestar de los dem&#225;s.</p>', 'Propuesta bilingüe | Colegio San Pablo', 'La formaci&#243;n en idiomas contin&#250;a ocupando un lugar destacado dentro de nuestra propuesta educativa.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (31, 'Actividades extracurriculares', 'La educaci&#243;n en Bachillerato trasciende la dimensi&#243;n acad&#233;mica.', '<h2>Formaci&#243;n integral y liderazgo</h2>
<p>La educaci&#243;n en Bachillerato trasciende la dimensi&#243;n acad&#233;mica.</p>
<p>Nuestros estudiantes participan en propuestas que favorecen el desarrollo de habilidades personales y sociales a trav&#233;s de:</p>
<p>Deportes y actividad f&#237;sica.</p>
<p>Arte y cultura.</p>
<p>Educaci&#243;n cristiana y formaci&#243;n en valores.</p>
<p>Proyectos solidarios y de servicio.</p>
<p>Actividades de liderazgo y participaci&#243;n estudiantil.</p>
<p>Viajes y experiencias educativas internacionales.</p>
<p>Tecnolog&#237;a e innovaci&#243;n.</p>
<p>Estas experiencias fortalecen la responsabilidad, el compromiso, la empat&#237;a y la capacidad de trabajar junto a otros.</p>
<h2>Arte y Cultura</h2>
<p>La creatividad, la sensibilidad y la expresi&#243;n ocupan un lugar importante dentro de nuestra propuesta educativa.</p>
<p>Promovemos experiencias art&#237;sticas y culturales que enriquecen la formaci&#243;n integral y permiten descubrir nuevas formas de comprender y expresar el mundo.</p>
<h3>ARTE Y CULTURA</h3>
<h2>Crear, expresar y descubrir nuevas formas de comprender el mundo</h2>
<p>La educaci&#243;n integral tambi&#233;n se construye a trav&#233;s de la creatividad, la sensibilidad y la capacidad de expresi&#243;n.</p>
<p>Promovemos experiencias art&#237;sticas y culturales que enriquecen la formaci&#243;n de nuestros estudiantes, favoreciendo el desarrollo de la imaginaci&#243;n, la apreciaci&#243;n est&#233;tica y la expresi&#243;n personal.</p>
<p>Creemos que el arte constituye una herramienta fundamental para comprender la realidad, comunicar ideas, expresar emociones y desarrollar una mirada m&#225;s amplia sobre el mundo que nos rodea.</p>
<h2>La creatividad como parte del aprendizaje</h2>
<p>La creatividad no pertenece &#250;nicamente al &#225;mbito art&#237;stico. Es una capacidad esencial para aprender, innovar y resolver desaf&#237;os.</p>
<p>Por ello generamos oportunidades para que nuestros estudiantes exploren diferentes formas de expresi&#243;n, experimenten con nuevos lenguajes y desarrollen confianza en sus propias capacidades creativas.</p>
<p>A trav&#233;s del arte, los estudiantes aprenden a observar, interpretar, imaginar y construir nuevas perspectivas.</p>
<h2>Expresar ideas y emociones</h2>
<p>Las experiencias art&#237;sticas ofrecen espacios valiosos para la comunicaci&#243;n y el desarrollo personal.</p>
<p>La m&#250;sica, las artes visuales, el teatro y otras manifestaciones culturales permiten a los estudiantes expresar pensamientos, sentimientos y experiencias de manera aut&#233;ntica y significativa.</p>
<p>Estas oportunidades fortalecen la autoestima, la sensibilidad y la capacidad de relacionarse con los dem&#225;s.</p>
<h2>Cultura que ampl&#237;a horizontes</h2>
<p>Promovemos el encuentro con diferentes expresiones culturales como una forma de enriquecer la formaci&#243;n humana y fortalecer la comprensi&#243;n de la diversidad.</p>
<p>A trav&#233;s de proyectos, presentaciones, actividades culturales y experiencias interdisciplinarias, los estudiantes desarrollan una mirada abierta y respetuosa hacia distintas formas de entender y expresar la realidad.</p>
<p>La cultura nos conecta con nuestra historia, nuestra comunidad y el mundo.</p>
<h2>Aprender a apreciar la belleza</h2>
<p>La educaci&#243;n art&#237;stica contribuye al desarrollo de la sensibilidad, la observaci&#243;n y la capacidad de valorar diferentes manifestaciones del talento humano.</p>
<p>Buscamos que nuestros estudiantes aprendan a disfrutar, comprender y apreciar el arte como una dimensi&#243;n importante de la vida y la experiencia humana.</p>
<h2>Arte, comunidad y participaci&#243;n</h2>
<p>Las actividades art&#237;sticas generan oportunidades de encuentro, colaboraci&#243;n y construcci&#243;n colectiva.</p>
<p>Presentaciones, exposiciones, celebraciones y proyectos culturales fortalecen los v&#237;nculos entre estudiantes, familias y educadores, enriqueciendo la vida de nuestra comunidad educativa.</p>
<h2>Formar personas creativas y sensibles</h2>
<p>Creemos que una educaci&#243;n integral debe favorecer tanto el desarrollo intelectual como la capacidad de imaginar, crear y expresarse.</p>
<p>Por eso promovemos experiencias art&#237;sticas y culturales que contribuyen a formar personas m&#225;s sensibles, reflexivas, creativas y capaces de aportar nuevas ideas al mundo que las rodea.</p>
<p>Porque aprender tambi&#233;n significa descubrir nuevas formas de mirar, sentir y transformar la realidad.</p>', 'Actividades extracurriculares | Colegio San Pablo', 'La educaci&#243;n en Bachillerato trasciende la dimensi&#243;n acad&#233;mica.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (32, 'Servicios educativos', 'Entendemos que educar implica acompa&#241;ar a cada joven en la b&#250;squeda de sentido, prop&#243;sito y direcci&#243;n para su futuro.', '<h2>Construir un proyecto de vida</h2>
<p>Entendemos que educar implica acompa&#241;ar a cada joven en la b&#250;squeda de sentido, prop&#243;sito y direcci&#243;n para su futuro.</p>
<p>Por eso promovemos espacios de reflexi&#243;n que permitan a los estudiantes reconocer sus fortalezas, explorar intereses y asumir decisiones responsables respecto a su desarrollo personal, acad&#233;mico y profesional.</p>
<p>Creemos que cada estudiante posee talentos &#250;nicos y el potencial para contribuir positivamente a la sociedad.</p>', 'Servicios educativos | Colegio San Pablo', 'Entendemos que educar implica acompa&#241;ar a cada joven en la b&#250;squeda de sentido, prop&#243;sito y direcci&#243;n para su futuro.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (33, 'Presentación', 'Ofrecemos acompa&#241;amiento acad&#233;mico personalizado para estudiantes que desarrollan trayectorias educativas con caracter&#237;sticas espec&#237;ficas, favoreciendo la organizaci&#243;n, la autonom&#237;a y el logro de sus objetivos de aprendizaje.', '<h3>Programa Libre Asistido</h3>
<p>Ofrecemos acompa&#241;amiento acad&#233;mico personalizado para estudiantes que desarrollan trayectorias educativas con caracter&#237;sticas espec&#237;ficas, favoreciendo la organizaci&#243;n, la autonom&#237;a y el logro de sus objetivos de aprendizaje.</p>
<h3>PROGRAMA LIBRE ASISTIDO</h3>
<h2>Una educaci&#243;n que se adapta a tu proyecto de vida</h2>
<p>No todos los estudiantes recorren el mismo camino.</p>
<p>Algunos dedican gran parte de su tiempo al deporte de alto rendimiento. Otros compatibilizan sus estudios con responsabilidades laborales o desean retomar una etapa educativa que qued&#243; pendiente.</p>
<p>Creemos que cada proyecto de vida merece una oportunidad para desarrollarse plenamente.</p>
<p>Por eso ofrecemos el Programa Libre Asistido, una propuesta de Bachillerato dise&#241;ada para quienes necesitan una modalidad flexible, sin renunciar a una formaci&#243;n acad&#233;mica de calidad ni al acompa&#241;amiento cercano que caracteriza a nuestra instituci&#243;n.</p>
<p>Porque una educaci&#243;n de calidad tambi&#233;n sabe adaptarse a las diferentes realidades de las personas.</p>', 'Presentación | Colegio San Pablo', 'Ofrecemos acompa&#241;amiento acad&#233;mico personalizado para estudiantes que desarrollan trayectorias educativas con caracter&#237;sticas espec&#237;ficas, favoreciendo la organizaci&#243;n, la autonom&#237;a y el logro de sus objetivos de aprendizaje.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (34, 'Inscripciones', 'El Programa Libre Asistido est&#225; dirigido a:', '<h2>Pensado para quienes construyen su futuro</h2>
<p>El Programa Libre Asistido est&#225; dirigido a:</p>
<p>J&#243;venes deportistas que necesitan compatibilizar sus entrenamientos y competencias con los estudios.</p>
<p>Estudiantes con responsabilidades laborales u horarios especiales.</p>
<p>Adultos que desean finalizar el Bachillerato.</p>
<p>Personas que mantienen asignaturas pendientes y buscan completar su formaci&#243;n.</p>
<p>Cada estudiante tiene una historia diferente. Nuestra propuesta busca acompa&#241;ar cada una de ellas con flexibilidad, compromiso y cercan&#237;a.</p>
<h2>Aprender sin renunciar a tus metas</h2>
<p>Muchos de nuestros estudiantes forman parte de las divisiones formativas de clubes deportivos y dedican sus ma&#241;anas a entrenamientos de alta exigencia.</p>
<p>Otros desarrollan una actividad laboral o enfrentan situaciones personales que dificultan la asistencia a un bachillerato tradicional.</p>
<p>El Programa Libre Asistido les permite continuar su formaci&#243;n acad&#233;mica mientras siguen construyendo su futuro en otros &#225;mbitos de su vida.</p>
<p>Creemos que estudiar y perseguir los propios sue&#241;os no deben ser caminos opuestos.</p>
<h2>Una modalidad flexible con acompa&#241;amiento permanente</h2>
<p>La propuesta combina encuentros presenciales y virtuales con el apoyo continuo de docentes tutores que acompa&#241;an el proceso de aprendizaje de cada estudiante.</p>
<p>La utilizaci&#243;n de plataformas educativas digitales, grupos reducidos y un seguimiento pedag&#243;gico personalizado favorecen una experiencia de aprendizaje cercana, organizada y adaptada a las necesidades de cada persona.</p>
<p>La flexibilidad de la modalidad no implica menor exigencia, sino una forma diferente de organizar el aprendizaje para facilitar el logro de los objetivos acad&#233;micos.</p>
<h2>Una organizaci&#243;n adaptada a tus tiempos</h2>
<p>El programa se desarrolla mediante cursos cuatrimestrales, permitiendo que cada estudiante planifique su recorrido acad&#233;mico de acuerdo con su disponibilidad y sus metas.</p>
<p>Esta organizaci&#243;n favorece un avance progresivo, compatible con la actividad deportiva, laboral o personal de cada participante.</p>
<p>Nuestro objetivo es ofrecer una propuesta exigente, pero al mismo tiempo posible y sostenible para quienes enfrentan desaf&#237;os que requieren otra forma de estudiar.</p>
<h2>Un equipo que acompa&#241;a</h2>
<p>Sabemos que detr&#225;s de cada estudiante existe una historia, un desaf&#237;o y un proyecto personal.</p>
<p>Por eso el acompa&#241;amiento cercano constituye uno de los pilares del Programa Libre Asistido.</p>
<p>Nuestros docentes y coordinadores trabajan junto a cada estudiante, orientando su proceso de aprendizaje y brindando el apoyo necesario para alcanzar sus objetivos acad&#233;micos.</p>
<p>Porque educar tambi&#233;n significa acompa&#241;ar.</p>
<h2>Construir el futuro, sin dejar de vivir el presente</h2>
<p>Finalizar el Bachillerato representa mucho m&#225;s que obtener un t&#237;tulo.</p>
<p>Significa abrir nuevas oportunidades para continuar estudios superiores, acceder a mejores posibilidades laborales y desarrollar plenamente el propio proyecto de vida.</p>
<p>Creemos que cada estudiante merece encontrar un camino que le permita crecer acad&#233;micamente sin abandonar aquello que lo apasiona.</p>
<p>Porque aprender juntos para la vida tambi&#233;n significa comprender que cada persona aprende, crece y construye su futuro de una manera diferente.</p>
<h2>Informaci&#243;n general</h2>
<p>El Programa Libre Asistido ofrece:</p>
<p>Modalidad semipresencial.</p>
<p>Cursos cuatrimestrales.</p>
<p>Encuentros presenciales y virtuales.</p>
<p>Docentes tutores.</p>
<p>Grupos reducidos.</p>
<p>Plataforma educativa digital.</p>
<p>Seguimiento pedag&#243;gico personalizado.</p>
<p>Si deseas conocer el funcionamiento del programa, las asignaturas disponibles o el proceso de inscripci&#243;n, nuestro equipo estar&#225; encantado de orientarte y acompa&#241;arte. (bot&#243;n para entrar en contacto)</p>', 'Inscripciones | Colegio San Pablo', 'El Programa Libre Asistido est&#225; dirigido a:', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (35, 'Identidad', 'Desde su fundaci&#243;n en 1948, hemos mantenido una estrecha vinculaci&#243;n con la tradici&#243;n cristiana luterana.', '<h2>Una tradici&#243;n que inspira nuestro presente</h2>
<p>Desde su fundaci&#243;n en 1948, hemos mantenido una estrecha vinculaci&#243;n con la tradici&#243;n cristiana luterana.</p>
<p>Esa herencia contin&#250;a inspirando nuestro proyecto educativo, fortaleciendo una visi&#243;n de la educaci&#243;n centrada en la persona, el aprendizaje permanente y el servicio a la comunidad.</p>
<p>La fe no se presenta como una imposici&#243;n, sino como una fuente de inspiraci&#243;n para construir relaciones m&#225;s humanas, una convivencia respetuosa y una sociedad mejor.</p>', 'Identidad | Colegio San Pablo', 'Desde su fundaci&#243;n en 1948, hemos mantenido una estrecha vinculaci&#243;n con la tradici&#243;n cristiana luterana.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (37, 'La confesionalidad en la práctica', 'Nuestra identidad cristiana inspira la vida institucional y contribuye a la formaci&#243;n &#233;tica y espiritual de nuestros estudiantes.', '<h2>Vida con valores</h2>
<p>Nuestra identidad cristiana inspira la vida institucional y contribuye a la formaci&#243;n &#233;tica y espiritual de nuestros estudiantes.</p>
<p>Promovemos valores como el respeto, la solidaridad, la responsabilidad y el servicio, favoreciendo el desarrollo de personas comprometidas con los dem&#225;s y con la sociedad.</p>
<h2>Liderazgo y Servicio</h2>
<p>Creemos que cada persona tiene la capacidad de generar un impacto positivo en su comunidad.</p>
<p>Por ello promovemos proyectos solidarios, actividades de participaci&#243;n y experiencias que fortalecen el liderazgo, la empat&#237;a y el compromiso social.</p>
<h3>VIDA CON VALORES</h3>
<h2>Educar con valores para servir a los dem&#225;s</h2>
<p>La vida con valores forma parte de la identidad y de la historia del Colegio San Pablo.</p>
<p>Inspirados en la tradici&#243;n cristiana luterana, promovemos una educaci&#243;n que integra conocimientos, valores y sentido de prop&#243;sito, contribuyendo al desarrollo de personas comprometidas consigo mismas, con los dem&#225;s y con la sociedad.</p>
<p>Creemos que educar implica acompa&#241;ar a cada estudiante en la construcci&#243;n de una vida basada en el respeto, la responsabilidad, la solidaridad y el servicio.</p>
<p>Por eso la formaci&#243;n cristiana constituye una dimensi&#243;n presente en la vida cotidiana de nuestra comunidad educativa.</p>
<h2>Una comunidad abierta y acogedora</h2>
<p>Recibimos con alegr&#237;a a estudiantes y familias de diferentes tradiciones, creencias y convicciones.</p>
<p>Nuestra identidad cristiana se expresa a trav&#233;s de una cultura institucional basada en el respeto, el di&#225;logo y la valoraci&#243;n de cada persona.</p>
<p>Creemos que la diversidad enriquece la convivencia y favorece la construcci&#243;n de una comunidad donde todos puedan sentirse acogidos, valorados y respetados.</p>
<h2>Aprender a vivir los valores</h2>
<p>Esta formaci&#243;n no se limita a la transmisi&#243;n de contenidos.</p>
<p>Buscamos que los valores se conviertan en experiencias concretas que acompa&#241;en la vida diaria de nuestros estudiantes.</p>
<p>A trav&#233;s de actividades formativas, proyectos solidarios, espacios de reflexi&#243;n, celebraciones y acciones de servicio, promovemos oportunidades para poner en pr&#225;ctica el compromiso con los dem&#225;s y la construcci&#243;n de una sociedad m&#225;s humana y fraterna.</p>
<h2>Formar personas para la vida</h2>
<p>Creemos que una educaci&#243;n verdaderamente integral debe contribuir no solo al desarrollo acad&#233;mico, sino tambi&#233;n a la formaci&#243;n del car&#225;cter, la conciencia &#233;tica y el compromiso con los dem&#225;s.</p>
<p>Por eso buscamos que nuestros estudiantes desarrollen conocimientos, habilidades y valores que les permitan vivir con responsabilidad, actuar con integridad y contribuir positivamente a la sociedad.</p>
<p>Porque educar tambi&#233;n significa aprender a servir, compartir y construir juntos un mundo mejor.</p>
<h3>LIDERAZGO Y SERVICIO</h3>
<h2>Liderar para servir</h2>
<p>Entendemos el liderazgo como la capacidad de influir positivamente en los dem&#225;s, asumir responsabilidades y contribuir al bienestar de la comunidad.</p>
<p>Creemos que liderar no significa ocupar un lugar de privilegio, sino poner los talentos, conocimientos y capacidades al servicio de otras personas.</p>
<p>Por eso promovemos experiencias que ayudan a nuestros estudiantes a desarrollar iniciativa, compromiso, empat&#237;a y sentido de responsabilidad, prepar&#225;ndolos para participar activamente en la construcci&#243;n de una sociedad mejor.</p>
<h2>Descubrir el propio potencial</h2>
<p>Cada estudiante posee habilidades, intereses y talentos &#250;nicos.</p>
<p>Nuestro desaf&#237;o consiste en ayudarlos a descubrir esas fortalezas y brindarles oportunidades para desarrollarlas de manera significativa.</p>
<p>A trav&#233;s de distintas experiencias educativas, los estudiantes aprenden a asumir desaf&#237;os, tomar decisiones, trabajar en equipo y reconocer el impacto que sus acciones tienen sobre quienes los rodean.</p>
<p>El liderazgo comienza por conocerse a uno mismo y aprender a actuar con responsabilidad.</p>
<h2>Aprender a comprometerse</h2>
<p>La formaci&#243;n integral implica comprender que cada persona forma parte de una comunidad y tiene la posibilidad de contribuir positivamente a ella.</p>
<p>Promovemos proyectos, actividades e iniciativas que fortalecen el compromiso con los dem&#225;s, favoreciendo el desarrollo de actitudes solidarias, colaborativas y respetuosas.</p>
<p>Buscamos que nuestros estudiantes comprendan que el aprendizaje adquiere un sentido m&#225;s profundo cuando se transforma en acciones concretas al servicio de otras personas.</p>
<h2>El valor del servicio</h2>
<p>El servicio ocupa un lugar central dentro de nuestra propuesta educativa.</p>
<p>A trav&#233;s de proyectos solidarios, campa&#241;as, actividades comunitarias y experiencias de participaci&#243;n, los estudiantes tienen la oportunidad de involucrarse con diferentes realidades y desarrollar una mayor sensibilidad frente a las necesidades de los dem&#225;s.</p>
<p>Estas experiencias fortalecen la empat&#237;a, la responsabilidad social y la capacidad de actuar con compromiso y generosidad.</p>
<h2>Aprender junto a otros</h2>
<p>Las experiencias de liderazgo permiten desarrollar habilidades fundamentales para la vida.</p>
<p>Entre ellas:</p>
<p>Comunicaci&#243;n efectiva.</p>
<p>Trabajo en equipo.</p>
<p>Resoluci&#243;n de conflictos.</p>
<p>Organizaci&#243;n y planificaci&#243;n.</p>
<p>Toma de decisiones.</p>
<p>Pensamiento cr&#237;tico.</p>
<p>Adaptabilidad.</p>
<p>Responsabilidad personal y colectiva.</p>
<p>Estas competencias contribuyen tanto al desarrollo acad&#233;mico como al crecimiento personal y social de los estudiantes.</p>
<h2>Ciudadanos comprometidos con su comunidad</h2>
<p>Vivimos en una sociedad que necesita personas capaces de participar activamente, colaborar con otros y asumir responsabilidades frente a los desaf&#237;os colectivos.</p>
<p>Por eso buscamos que nuestros estudiantes desarrollen una actitud comprometida con su entorno, comprendiendo que cada acci&#243;n puede generar un impacto positivo en la vida de otras personas.</p>
<p>Educar para la ciudadan&#237;a implica formar personas que no solo conocen la realidad, sino que tambi&#233;n est&#225;n dispuestas a transformarla.</p>
<h2>Formar personas que hagan la diferencia</h2>
<p>M&#225;s all&#225; de los logros individuales, aspiramos a que nuestros estudiantes desarrollen la capacidad de actuar con integridad, sensibilidad y compromiso frente a las necesidades del mundo que los rodea.</p>
<p>Porque creemos que los verdaderos l&#237;deres son aquellos que utilizan sus talentos para construir oportunidades, fortalecer comunidades y servir a los dem&#225;s.</p>
<p>Y porque aprender juntos para la vida tambi&#233;n significa aprender a contribuir, compartir y generar un impacto positivo en la sociedad.</p>', 'La confesionalidad en la práctica | Colegio San Pablo', 'Nuestra identidad cristiana inspira la vida institucional y contribuye a la formaci&#243;n &#233;tica y espiritual de nuestros estudiantes.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

INSERT INTO sub_menu_paginas (id_sub_menu, titulo, bajada, contenido, meta_title, meta_description, actualizado_en, actualizado_por) VALUES (39, 'Educación cristiana', 'Nuestra propuesta educativa se inspira en los principios de la tradici&#243;n cristiana luterana, promoviendo una formaci&#243;n basada en el amor al pr&#243;jimo, la b&#250;squeda de la verdad, la responsabilidad personal y el servicio a la comunidad.', '<h2>Educaci&#243;n cristiana luterana</h2>
<p>Nuestra propuesta educativa se inspira en los principios de la tradici&#243;n cristiana luterana, promoviendo una formaci&#243;n basada en el amor al pr&#243;jimo, la b&#250;squeda de la verdad, la responsabilidad personal y el servicio a la comunidad.</p>
<p>La educaci&#243;n cristiana forma parte de la vida institucional y contribuye al desarrollo espiritual y &#233;tico de nuestros estudiantes, en un marco de respeto y apertura hacia personas de diferentes creencias y convicciones.</p>
<p>Creemos que cada estudiante es &#250;nico, valioso y capaz de desarrollar plenamente los dones que ha recibido para construir un proyecto de vida con prop&#243;sito.</p>
<h2>Una educaci&#243;n inspirada en el Evangelio</h2>
<p>Nuestra propuesta educativa se fundamenta en principios cristianos que orientan la convivencia, las relaciones humanas y el compromiso con el pr&#243;jimo.</p>
<p>Promovemos valores como:</p>
<p>El respeto por la dignidad de cada persona.</p>
<p>La honestidad y la responsabilidad.</p>
<p>La solidaridad y la empat&#237;a.</p>
<p>La b&#250;squeda de la verdad.</p>
<p>El servicio a los dem&#225;s.</p>
<p>El compromiso con la justicia y el bien com&#250;n.</p>
<p>Estos principios acompa&#241;an el desarrollo integral de nuestros estudiantes y enriquecen su formaci&#243;n personal.</p>
<h2>Fe, reflexi&#243;n y crecimiento personal</h2>
<p>Entendemos que la educaci&#243;n tambi&#233;n contribuye al desarrollo espiritual de las personas.</p>
<p>Por ello generamos espacios que favorecen la reflexi&#243;n, el di&#225;logo, la b&#250;squeda de sentido y el crecimiento interior, acompa&#241;ando a los estudiantes en la construcci&#243;n de una mirada trascendente sobre la vida y sus desaf&#237;os.</p>
<p>Buscamos formar personas capaces de actuar con libertad, responsabilidad y conciencia &#233;tica frente a las decisiones que deber&#225;n asumir a lo largo de su vida.</p>', 'Educación cristiana | Colegio San Pablo', 'Nuestra propuesta educativa se inspira en los principios de la tradici&#243;n cristiana luterana, promoviendo una formaci&#243;n basada en el amor al pr&#243;jimo, la b&#250;squeda de la verdad, la responsabilidad personal y el servicio a la comunidad.', NOW(), NULL) ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), bajada=VALUES(bajada), contenido=VALUES(contenido), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), actualizado_en=VALUES(actualizado_en), actualizado_por=VALUES(actualizado_por);

COMMIT;

-- Comprobaciones
SELECT m.id_menu, m.nombre AS menu, sm.id_sub_menu, sm.nombre AS submenu, sp.id_pagina, sp.titulo, sp.bajada, CHAR_LENGTH(sp.contenido) AS caracteres_contenido, sp.actualizado_en FROM menus m INNER JOIN sub_menus sm ON sm.id_menu=m.id_menu LEFT JOIN sub_menu_paginas sp ON sp.id_sub_menu=sm.id_sub_menu ORDER BY m.orden, sm.orden;
SELECT id_sub_menu, COUNT(*) AS total_paginas FROM sub_menu_paginas GROUP BY id_sub_menu HAVING COUNT(*) > 1;
SELECT id_pagina,id_sub_menu,titulo,bajada FROM sub_menu_paginas WHERE contenido LIKE '%Poemas de un novelista%' OR contenido LIKE '%José Donoso%' OR titulo LIKE '%qwe%' OR bajada='Bajada' OR boton_url LIKE '%google.cl%';
