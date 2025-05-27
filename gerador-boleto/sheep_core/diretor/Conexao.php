<?php
/**************************************************************************
 * ************************************************************************
 * GERENTE DE CONEXAO - PROGRAMADOR MAYKON SILVEIRA - MAYKONSILVEIRA.COM.BR
 * 
 * ********************************************************************
 * ********************************************************************
* MAYKONSILVEIRA.COM.BR DEREICIONANDO VOCÊ PARA O CAMINHO DO SUCESSO #*
 * *************MAYKON***SILVEIRA**************************************
 * *************WEBTEC**TECHNOLOGIES***********************************
 * ********************************************************************
 * TUDO AQUI FOI CRIADO NO MÊS DE NOVEMBRO DE 2020 POR MAYKON SILVEIRA
 * TODOS OS DIREITOS RESERVADOS E CÓDIGO FONTE RASTREADO COM ARQUIVOS 
 * CRIADO POR MAYKONSILVEIRA.COM.BR DESDE 2007 *********
 * TODA SABEDORIA PARA CRIAR ESTES SISTEMAS VEM DO SANTO E ETERNOR PAI
 * O SANTO SENHOR DEUS DE ABRAÃO, ISSAC E JACÓ E DO MEU ÚNICO SENHOR 
 * O MESSIAS NOSSO SALVADOR, POIS A GLROIA É DO PAI E DO FILHO PARA SEMPRE
 * ***********************************************************************
 * ***********************************************************************
 */
class Conexao{

    private static $sheepHost = SHEEP_HOST;
    private static $sheepUsuario = SHEEP_USER;
    private static $sheepSenha = SHEEP_SENHA;
    private static $sheepDb = SHEEP_BD;


    /** @var PDO */
    private static $Canectar = null;

    /**
     * Conecta com o banco de dados com o pattern singleton.
     * Retorna um objeto PDO!
     */
    private static function Conectar() {
        try {
            if (self::$Canectar == null):
                $dsn = 'mysql:host=' . self::$sheepHost . ';dbname=' . self::$sheepDb;
                $op = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];
                self::$Canectar = new PDO($dsn, self::$sheepUsuario, self::$sheepSenha, $op);
            endif;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die;
        }

        self::$Canectar->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$Canectar;
    }

    /** Retorna um objeto PDO Singleton Pattern. */
    public static function getCanectar() {
        return self::Conectar();
    }

}
