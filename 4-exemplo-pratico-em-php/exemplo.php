<?php

/*
 * ATENÇÃO
 *
 * O exemplo abaixo tem o único propósito de ser o mais didático possível quanto
 * à implementação de TRAVAS OTIMISTAS,
 * e por isso preferi abrir mão de algumas boas práticas em troca da didática.
 *
 * Cada conexão de banco ($db1, $db2 e $db3) representa um usuário diferente.
 *
 * Boas práticas que deveriam ter sido respeitadas, mas não foram:
 *  - Orientação a objetos
 *  - ORM (Doctrine, etc)
 *  - Multicamada (MVC, etc)
 *  - Evitar duplicação de código
 */

/*
 * 1) Criaremos um novo banco sqlite
 */
$db1 = new PDO('sqlite:blog.db');
$db2 = new PDO('sqlite:blog.db');
$db3 = new PDO('sqlite:blog.db');

$db3->exec("DROP TABLE IF EXISTS ArtigoDeBlog");

$db3->exec("
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
 * 3) Versão inicial
 */
$st3 = $db3->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo3 = $st3->fetchAll(PDO::FETCH_ASSOC)[0];
echo "Versão inicial:\n";
echo json_encode($artigo3);
echo "\n\n";

/*
 * 4) Usuário administrador carrega o artigo
 */
$st2 = $db2->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo2 = $st2->fetchAll(PDO::FETCH_ASSOC)[0];

/*
 * 5) Usuário autor carrega o artigo
 */
$st1 = $db1->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo1 = $st1->fetchAll(PDO::FETCH_ASSOC)[0];

/*
 * 6) Usuário administrador corrige ortografia
 */
$artigo2['conteudo'] = 'Este post é muito bacana! Show de bola.';
$sql2 = "UPDATE ArtigoDeBlog SET
    conteudo = :conteudo,
    versao = versao + 1
    WHERE id = 1 AND versao = :versao
";
$st2 = $db2->prepare($sql2);
$st2->bindValue(':conteudo', $artigo2['conteudo']);
$st2->bindValue(':versao', $artigo2['versao']);
$st2->execute();
if ($st2->rowCount() > 0) {
    echo "Atualização ortográfica realizada!\n\n";
} else {
    echo "Atualização ortográfica NÃO realizada!\n\n";
}

/*
 * 7) Usuário autor corrige o título
 */
$artigo1['titulo'] = 'Meu post massa';
$sql1 = "UPDATE ArtigoDeBlog SET
    titulo = :titulo,
    versao = versao + 1
    WHERE id = 1 AND versao = :versao
";
$st1 = $db1->prepare($sql1);
$st1->bindValue(':titulo', $artigo1['titulo']);
$st1->bindValue(':versao', $artigo1['versao']);
$st1->execute();
if ($st1->rowCount() > 0) {
    echo "Atualização do título realizada!\n\n";
} else {
    echo "Atualização do título NÃO realizada!\n\n";
    /* Após receber mensagem de erro, usuário concilia os
     * dados tenta novamente...
     */
    $st1 = $db1->query("
        SELECT * FROM ArtigoDeBlog WHERE id = 1
    ");
    $artigo1 = $st1->fetchAll(PDO::FETCH_ASSOC)[0];
    $artigo1['titulo'] = 'Meu post massa';
    $sql1 = "UPDATE ArtigoDeBlog SET
        titulo = :titulo,
        versao = versao + 1
        WHERE id = 1 AND versao = :versao
    ";
    $st1 = $db1->prepare($sql1);
    $st1->bindValue(':titulo', $artigo1['titulo']);
    $st1->bindValue(':versao', $artigo1['versao']);
    $st1->execute();
    if ($st1->rowCount() > 0) {
        echo "Atualização do título realizada!\n\n";
    } else {
        echo "Atualização do título NÃO realizada!\n\n";
    }
}

/*
 * 8) Versão final, acessada por um terceiro usuário
 */
$st3 = $db3->query("
    SELECT * FROM ArtigoDeBlog WHERE id = 1
");
$artigo3 = $st3->fetchAll(PDO::FETCH_ASSOC)[0];
echo "Versão final:\n";
echo json_encode($artigo3);
echo "\n";
