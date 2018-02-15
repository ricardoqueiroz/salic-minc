<?php
class tbPlanilhaAprovacao extends MinC_Db_Table_Abstract
{
    protected $_schema = "sac";
    protected $_name = "tbPlanilhaAprovacao";
    protected $_primary = "idPlanilhaAprovacao";


    public function init()
    {
        parent::init();
    }

    public function cadastrarDados($dados)
    {
        return $this->insert($dados);
    }

    public function alterarDados($dados, $where)
    {
        $where = "idPlanilhaAprovacao = " . $where;
        return $this->update($dados, $where);
    }


    public function buscarItensOrcamentarios($where, $order = array())
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);

        $slct->from(
            array('a' => $this->_name),
            array('idPlanilhaItem')
        );

        $slct->joinLeft(
            array('b' => 'tbPlanilhaItens'),
            "a.idPlanilhaItem = b.idPlanilhaItens",
            array('Descricao'),
            'SAC.dbo'
        );

        // adicionando clausulas where
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        $slct->group('a.idPlanilhaItem');
        $slct->group('b.Descricao');

        $slct->order($order);


        return $this->fetchAll($slct);
    }

    public function copiandoPlanilhaRecurso($idPronac)
    {
        $sql = "INSERT INTO SAC.dbo.tbPlanilhaAprovacao
                    (tpPlanilha,dtPlanilha,idPlanilhaProjeto,idPlanilhaProposta,idPronac,idProduto,idEtapa,idPlanilhaItem,dsItem,idUnidade,
                    qtItem,nrOcorrencia,vlUnitario,qtDias,tpDespesa,tpPessoa,nrContraPartida,nrFonteRecurso,idUFDespesa,idMunicipioDespesa,
                    dsJustificativa,idAgente,StAtivo)
              SELECT 'CO',GETDATE(),idPlanilhaProjeto,idPlanilhaProposta,'$idPronac',idProduto,idEtapa,idPlanilhaItem,Descricao,idUnidade,
                        Quantidade,Ocorrencia,ValorUnitario,QtdeDias,TipoDespesa,TipoPessoa,Contrapartida,FonteRecurso,UFDespesa,
                        MunicipioDespesa,Justificativa,idUsuario,'S'
                        FROM SAC.dbo.tbPlanilhaProjeto
                        WHERE idPronac = '$idPronac'
        ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function copiandoPlanilhaRemanejamento($idPronac)
    {
        $sql = "INSERT INTO SAC.dbo.tbPlanilhaAprovacao
                        (tpPlanilha,dtPlanilha,idPlanilhaProjeto,idPlanilhaProposta,idPronac,idProduto,idEtapa,idPlanilhaItem,dsItem,idUnidade,
                        qtItem,nrOcorrencia,vlUnitario,qtDias,tpDespesa,tpPessoa,nrContraPartida,nrFonteRecurso,idUFDespesa,idMunicipioDespesa,
                        dsJustificativa,idAgente,StAtivo)
               SELECT 'RP',GETDATE(),idPlanilhaProjeto,idPlanilhaProposta,'$idPronac',idProduto,idEtapa,idPlanilhaItem,dsItem,idUnidade,
                        qtItem,nrOcorrencia,vlUnitario,qtDias,tpDespesa,tpPessoa,nrContraPartida,nrFonteRecurso,idUFDespesa,idMunicipioDespesa,
                        dsJustificativa,idAgente,'N'
                        FROM SAC.dbo.tbPlanilhaAprovacao
                        WHERE idPronac = '$idPronac'
        ";

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function buscarDadosAvaliacaoDeItemRemanejamento($where = array())
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr(
                    'a.idPRONAC,
                    a.idPlanilhaAprovacao,
                    a.idUFDespesa,
                    a.idMunicipioDespesa,
                    a.idProduto,
                    a.nrFonteRecurso,
                    b.Descricao as descProduto,
                    a.idEtapa,
                    c.Descricao as descEtapa,
                    a.idPlanilhaItem,
                    d.Descricao as descItem,
                    a.idUnidade,
                    e.Descricao as descUnidade,
                    a.qtItem as Quantidade,
                    a.nrOcorrencia as Ocorrencia,
                    a.vlUnitario as ValorUnitario,
                    a.qtDias as QtdeDias,
                    CAST(a.dsJustificativa as TEXT) as Justificativa,
                    a.idAgente'
                )
            )
        );
        $select->joinLeft(
            array('b' => 'Produto'),
            "a.idProduto = b.Codigo",
            array(),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbPlanilhaEtapa'),
            "a.idEtapa = c.idPlanilhaEtapa",
            array(),
            'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'tbPlanilhaItens'),
            "a.idPlanilhaItem = d.idPlanilhaItens",
            array(),
            'SAC.dbo'
        );
        $select->joinInner(
            array('e' => 'tbPlanilhaUnidade'),
            "a.idUnidade = e.idUnidade",
            array(),
            'SAC.dbo'
        );

        foreach ($where as $key => $valor) {
            $select->where($key, $valor);
        }

        return $this->fetchAll($select);
    }

    public function valorTotalPlanilha($where = array())
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr('ROUND(SUM(a.qtItem*a.nrOcorrencia*a.vlUnitario), 2) AS Total')
            )
        );

        foreach ($where as $key => $valor) {
            $select->where($key, $valor);
        }


        return $this->fetchAll($select);
    }


    public function getInfoIdPlanilhaPai($idPlanilhaAprovacao, $tpPlanilha = null)
    {
        $idPlanilhaAprovacaoPai = array();

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr('a.idPlanilhaAprovacao, a.idPlanilhaAprovacaoPai, a.tpAcao, a.tpPlanilha')
            )
        );

        $select->where(new Zend_Db_Expr('(idPlanilhaAprovacao = ?) OR (idPlanilhaAprovacaoPai = ?)', $idPlanilhaAprovacao));
        if ($tpPlanilha) {
            $select->where('tpPlanilha = ?', $tpPlanilha);
        }

        return $this->fetchAll($select);
    }
}
