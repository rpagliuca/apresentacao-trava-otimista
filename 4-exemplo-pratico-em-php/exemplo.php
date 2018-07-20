<?php

/*
 * 1) Criaremos um novo banco sqlite
 */
$db1 = new PDO('sqlite:blog.db');
$db2 = new PDO('sqlite:blog.db');
$db3 = new PDO('sqlite:blog.db');

$db1->exec("
    CREATE TABLE ArtigoDeBlog (
        id INT PRIMARY KEY,
        titulo TEXT,
        data DATETIME,
        conteudo TEXT,
        id_autor INT,
        versao INT 
    )
");

/*
 * 2) Usuário autor cria um novo artigo
 */
$db1->exec("
    INSERT INTO ArtigoDeBlog (id, titulo, data, conteudo, id_autor, versao)
    VALUES (
        1,
        'Meu post bacana',
        '2018-07-20',
        'Ece pousti é muito bacana! Chou de bola.',
        2,
        '1'
    )
");

/*
 * 3) Usuário administrador carrega o artigo
 */
$st2 = $db2->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo2 = $st2->fetchAll(PDO::FETCH_ASSOC)[0];

/*
 * 4) Usuário autor carrega o artigo
 */
$st1 = $db1->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo1 = $st1->fetchAll(PDO::FETCH_ASSOC)[0];

/*
 * 5) Usuário administrador corrige ortografia
 */
$artigo2['conteudo'] = 'Este post é muito bacana! Show de bola.';
$sql2 = "UPDATE ArtigoDeBlog SET conteudo = '{$artigo2['conteudo']}',
    versao = versao + 1
    WHERE id = 1 AND versao = '{$artigo2['versao']}'
";
$st2 = $db2->prepare($sql2);
$st2->execute();
if ($st2->rowCount() > 0) {
    echo "Atualização ortográfica realizada!\n";
} else {
    echo "Atualização ortográfica NÃO realizada!\n";
}

/*
 * 6) Usuário autor corrige o título
 */
$artigo1['titulo'] = 'Meu post massa';
$sql1 = "UPDATE ArtigoDeBlog SET titulo = '{$artigo1['titulo']}',
    versao = versao + 1
    WHERE id = 1 AND versao = '{$artigo1['versao']}'
";
$st1 = $db1->prepare($sql1);
$st1->execute();
if ($st1->rowCount() > 0) {
    echo "Atualização do título realizada!\n";
} else {
    echo "Atualização do título NÃO realizada!\n";
}

/*
 * 7) Versão final
 */
$st3 = $db3->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo3 = $st3->fetchAll(PDO::FETCH_ASSOC)[0];
echo "Versão final:\n";
echo json_encode($artigo3);
echo "\n";
