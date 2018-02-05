<?php
/**
 * DAO PlanoDistribuicaoProduto
 * @package application
 * @subpackage application.model
 * @link http://www.cultura.gov.br
 *
 */

class Proposta_Model_DbTable_PlanoDistribuicaoProduto extends MinC_Db_Table_Abstract
{
    protected $_schema = 'sac';
    protected $_name   = 'PlanoDistribuicaoProduto';
    protected $_primary = 'idPlanoDistribuicao';

    /**
     * Metodo para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o ultimo id cadastrado)
     */
    public function cadastrarDados($dados)
    {
        return $this->insert($dados);
    } // fecha metodo cadastrarDados()


    public function buscarPlanoDeDistribuicao($idPronac)
    {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
                array('a' => $this->_name),
                array('idPlanoDistribuicao','idProduto','QtdePatrocinador','QtdeProponente','QtdeOutros')
        );
        $a->joinInner(
                array('b' => 'Projetos'),
            "a.idProjeto = b.idProjeto",
                array('IdPRONAC'),
            'SAC.dbo'
        );
        $a->joinInner(
                array('c' => 'Produto'),
            "a.idProduto = c.Codigo",
                array('Descricao as Produto'),
            'SAC.dbo'
        );
        $a->where('b.IdPRONAC = ?', $idPronac);
        return $this->fetchAll($a);
    }

    public function buscarProdutosProjeto($idPronac)
    {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
                array('a' => $this->_name),
                array('stPrincipal','idProduto')
        );
        $a->joinInner(
                array('b' => 'Projetos'),
            "a.idProjeto = b.idProjeto",
                array(''),
            'SAC.dbo'
        );
        $a->joinInner(
                array('c' => 'Produto'),
            "a.idProduto = c.Codigo",
                array('Descricao as Produto'),
            'SAC.dbo'
        );
        $a->joinInner(
                array('d' => 'tbAnaliseDeConteudo'),
            "a.idProduto = d.idProduto AND b.IdPRONAC = d.idPronac",
                array('*'),
            'SAC.dbo'
        );
        $a->where('b.IdPRONAC = ?', $idPronac);
        $a->where('d.idPronac = ?', $idPronac);
        $a->order(array('1 DESC', '3 ASC'));

        return $this->fetchAll($a);
    }

    public function comboProdutosParaInclusaoReadequacao($idPronac)
    {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
            array('a' => $this->_name),
            array('idProduto')
        );
        $a->joinInner(
            array('b' => 'Produto'),
            "a.idProduto = b.codigo",
            array('Descricao AS Produto'),
            'SAC.dbo'
        );
        $a->joinInner(
            array('c' => 'Projetos'),
            "a.idProjeto = c.idProjeto",
            array(''),
            'SAC.dbo'
        );
        $a->where('c.IdPRONAC = ?', $idPronac);


        $b = $this->select();
        $b->setIntegrityCheck(false);
        $b->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("'0', 'Administra&ccedil;&atilde;o do Projeto'")
            )
        );
        $b->joinInner(
            array('c' => 'Projetos'),
            "a.idProjeto = c.idProjeto",
            array(''),
            'SAC.dbo'
        );
        $b->where('c.IdPRONAC = ?', $idPronac);


        $slctUnion = $this->select()
            ->union(array('('.$a.')', '('.$b.')'))
            ->order('1', '2');

        return $this->fetchAll($slctUnion);
    }

    public function buscarDadosCadastrarProdutos($idPreProjeto, $idProduto)
    {
        $select = $this->select();

        $select->setIntegrityCheck(false);

        $select->distinct(true);

        $select->from(
            array('pd'=>$this->getName('PlanoDistribuicaoProduto')),
            array('CodigoProduto'=>'pd.idproduto',
                'idProposta'=> 'pd.idprojeto'
            ),
            $this->_schema
        );

        $select->where('idproduto = ?', $idProduto);

        $select->where('idprojeto = ?', $idPreProjeto);

        $select->where('pd.stplanodistribuicaoproduto = ?', 1);

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($select);
    }

    public function obterUfsMunicipiosDoDetalhamento($idPreProjeto) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('p' => $this->_name),
            array(),
            $this->_schema
        );
        $select->joinInner(
            array('d' => 'tbDetalhaPlanoDistribuicao'),
            "p.idPlanoDistribuicao = d.idPlanoDistribuicao",
            array('d.idUF', 'd.idMunicipio'),
            $this->_schema
        );
        $select->joinInner(
            array('u' => 'uf'),
            "d.idUF = u.idUf",
            array('u.Sigla as UF'),
            $this->getSchema('agentes')
        );
        $select->where('p.idProjeto = ?', $idPreProjeto);
        return $this->fetchAll($select);
    }

    public function validatePlanoDistribuicao($idPreProjeto)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);

        $select->from(
            array('pd'=>$this->getName('PlanoDistribuicaoProduto')),
            array('pd.canalAberto', 'pd.PrecoUnitarioNormal')
        )
            ->joinInner(
                array('p' => 'Produto'),
                "pd.idProduto = p.Codigo",
                array('Descricao as Produto'),
                'SAC.dbo'
            )
            ->where('pd.idProjeto = ?' , $idPreProjeto);
        
        $planosDistribuicao = $this->fetchAll($select);

        $errors = array();
        
        foreach ($planosDistribuicao as $planoDistribuicao) {
            if (!$planoDistribuicao['canalAberto']) {
                if ((int)$planoDistribuicao['PrecoUnitarioNormal'] > 225) {
                    $error = new StdClass();
                    $error->idPreProjeto = $idPreProjeto;
                    $error->dsChamada = '';
                    $error->dsInconsistencia = "O pre&ccedil;o m&eacute;dio do produto '" . $planoDistribuicao['Produto'] . "' ultrapassou o limite de 225,00.";
                    $error->Observacao = 'PENDENTE';
                    
                    $errors[] = $error;
                }
            }
        }
        
        return $errors;        
    }
}
