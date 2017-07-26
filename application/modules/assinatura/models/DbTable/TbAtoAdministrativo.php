<?php

class Assinatura_Model_DbTable_TbAtoAdministrativo extends MinC_Db_Table_Abstract
{
    protected $_schema = 'sac';
    protected $_name = 'tbAtoAdministrativo';
    protected $_primary = 'idAtoAdministrativo';

    public function obterPerfilAssinante($idOrgaoDoAssinante, $idPerfilDoAssinante, $idTipoDoAto)
    {

        $objQuery = $this->select();
        $objQuery->setIntegrityCheck(false);
        $objQuery->from(
            $this->_name,
            array(
                'idAtoAdministrativo',
                'idTipoDoAto',
                'idCargoDoAssinante',
                'idPerfilDoAssinante',
                'idOrgaoDoAssinante',
                'idOrdemDaAssinatura'
            ),
            $this->_schema
        );
        $objQuery->joinInner(
            array('Verificacao' => 'Verificacao'),
            'Verificacao.idVerificacao = tbAtoAdministrativo.idCargoDoAssinante',
            array('dsCargoAssinante' => 'Verificacao.Descricao'),
            $this->getSchema('Agentes')
        );
        $objQuery->joinInner(
            array('grupos' => 'Grupos'),
            'grupos.gru_codigo = tbAtoAdministrativo.idPerfilDoAssinante',
            array('dsPerfil' => 'grupos.gru_nome'),
            $this->getSchema('Tabelas')
        );
        $objQuery->where('idOrgaoDoAssinante = ?', $idOrgaoDoAssinante);
        $objQuery->where('idPerfilDoAssinante = ?', $idPerfilDoAssinante);
        $objQuery->where('idTipoDoAto = ?', $idTipoDoAto);

        $result = $this->fetchRow($objQuery);
        if ($result) {
            return $result->toArray();
        }
    }

    public function obterQuantidadeMinimaAssinaturas($idTipoDoAto)
    {
        $objQuery = $this->select();
        $objQuery->setIntegrityCheck(false);
        $objQuery->from(
            $this->_name,
            array(
                'quantidade_assinaturas' => new Zend_Db_Expr(
                    "count(*)"
                )
            ),
            $this->_schema
        );
        $objQuery->where('idTipoDoAto = ?', $idTipoDoAto);
        $objQuery->where('stEstado = ?', true);
        $objResultado = $this->fetchRow($objQuery);
        if ($objResultado) {
            $resultadoArray = $objResultado->toArray();
            return $resultadoArray['quantidade_assinaturas'];
        }
    }

    /**
     * @return string Código do orgao
     */
    public function obterProximoOrgaoDeDestino($idTipoDoAto, $idOrdemDaAssinaturaAtual)
    {

        $objQuery = $this->select();
        $objQuery->setIntegrityCheck(false);
        $objQuery->from(
            $this->_name,
            'idOrgaoDoAssinante',
            $this->_schema
        );
        $objQuery->where('idTipoDoAto = ?', $idTipoDoAto);
        $objQuery->where('idOrdemDaAssinatura > ?', $idOrdemDaAssinaturaAtual);
        $objQuery->order('idOrdemDaAssinatura asc');
        $objQuery->limit(1);

        $objResultado = $this->fetchRow($objQuery);
        if ($objResultado) {
            $arrayResultado = $objResultado->toArray();
            return $arrayResultado['idOrgaoDoAssinante'];
        }
    }

    public function obterAtoAdministrativoAtual($idTipoDoAto, $idPerfilDoAssinante, $idOrgaoDoAssinante)
    {
        $objQuery = $this->select();
        $objQuery->setIntegrityCheck(false);
        $objQuery->from(
            $this->_name,
            '*',
            $this->_schema
        );
        $objQuery->where('idTipoDoAto = ?', $idTipoDoAto);
        $objQuery->where('idPerfilDoAssinante = ?', $idPerfilDoAssinante);
        $objQuery->where('idOrgaoDoAssinante = ?', $idOrgaoDoAssinante);
        $objResultado = $this->fetchRow($objQuery);
        if ($objResultado) {
            return $objResultado->toArray();
        }
    }

    /**
     * Transposição da view "vwAtoAdministrativo".
     */
    public function obterAtoAdministrativoDetalhado() {
        $objQuery = $this->select();
        $objQuery->setIntegrityCheck(false);
        $objQuery->from(
            $this->_name,
            '*',
            $this->_schema
        );

        $objQuery->joinInner(
            array("Verificacao" => "Verificacao"),
            "{$this->_name}.idTipoDoAto = Verificacao.idVerificacao",
            array("dsAtoAdministrativo" => "Descricao"),
            $this->_schema
        );

        $objQuery->joinInner(
            array("Verificacao_Agentes" => "Verificacao"),
            "{$this->_name}.idCargoDoAssinante = Verificacao_Agentes.idVerificacao",
            array("dsCargoDoAssinante" => "Descricao"),
            "Agentes"
        );

        $objQuery->joinInner(
            array("Orgaos" => "Orgaos"),
            "{$this->_name}.idOrgaoDoAssinante = Orgaos.Codigo",
            array("dsOrgaoDoAssinante" => "Sigla"),
            $this->_schema
        );

        $objQuery->joinInner(
            array("Grupos" => "Grupos"),
            "{$this->_name}.idPerfilDoAssinante = Grupos.gru_codigo",
            array("dsPerfil" => "gru_nome"),
            'tabelas'
        );

        return $this->fetchAll($objQuery)->toArray();
    }

}