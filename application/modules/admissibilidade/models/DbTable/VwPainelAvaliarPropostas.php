<?php

/**
 * View para painel de avaliação das propostas e tranformação em projetos.
 *
 * @link http://salic.cultura.gov.br
 */
class Admissibilidade_Model_DbTable_VwPainelAvaliarPropostas extends MinC_Db_Table_Abstract
{
    protected $_schema = 'sac';
    protected $_name = 'vwPainelAvaliarPropostas';
    protected $_primary = 'idProjeto';

    public function propostas($where = array(), $order = array(), $start = 0, $limit = 10, $search = null)
    {
        $db = $this->getAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        $sql = $db->select()
            ->from('vwPainelAvaliarPropostas', '*', $this->_schema);

        foreach ($where as $coluna => $valor) {
            $sql->where($coluna, $valor);
        }

        if (!empty($search['value'])) {
            $sql->where('idProjeto like ? OR NomeProposta like ? OR Tecnico like ?', '%' . $search['value'] . '%');
        }

        $sql->order($order);

        if (!is_null($start) && $limit) {
            $start = (int)$start;
            $limit = (int)$limit;
            $sql->limitPage($start, $limit);
        }

        return $db->fetchAll($sql);
    }

    public function obterPropostasParaAvaliacao(
        $where = [],
        $order = [],
        $start = 0,
        $limit = 10,
        $search = null,
        Admissibilidade_Model_DistribuicaoAvaliacaoProposta $distribuicaoAvaliacaoProposta = null
    )
    {
        $db = $this->getAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        $subSelect = $this->select();
        $subSelect->setIntegrityCheck(false);
        $subSelect->from('vwPainelAvaliarPropostas',
            ['*'],
            $this->_schema);

        foreach ($where as $coluna => $valor) {
            $subSelect->where($coluna, $valor);
        }

        if (!empty($search['value'])) {
            $subSelect->where('idProjeto like ? OR NomeProposta like ? OR Tecnico like ?', "%{$search['value']}%");
        }

        if (!is_null($start) && $limit) {
            $start = (int)$start;
            $limit = (int)$limit;
            $subSelect->limitPage($start, $limit);
        }

        $sqlPerfisDistribuicao = '';
        if($distribuicaoAvaliacaoProposta->getIdPerfil() != Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE) {
            $perfis = [
                $distribuicaoAvaliacaoProposta->getIdPerfil()
            ];

            if ($distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE) {
                $perfis[] = Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE;
            }
            $perfisDistribuicao = implode(',', $perfis);
            $sqlPerfisDistribuicao = " and distribuicao_avaliacao_proposta.id_perfil in ({$perfisDistribuicao}) ";
        }

        $subSelect->joinLeft(
            ['distribuicao_avaliacao_proposta']
            , "distribuicao_avaliacao_proposta.id_preprojeto = vwPainelAvaliarPropostas.idProjeto
                    {$sqlPerfisDistribuicao}
                    and distribuicao_avaliacao_proposta.id_orgao_superior = {$distribuicaoAvaliacaoProposta->getIdOrgaoSuperior()}"
            ,
            [
                'avaliacao_atual' => "coalesce(distribuicao_avaliacao_proposta.avaliacao_atual, '0')",
                'quantidade_distribuicoes' => "coalesce(distribuicao_avaliacao_proposta.id_distribuicao_avaliacao_proposta, '0')"
            ]
            ,  $this->getSchema('sac')
        );

        $selectFinal = $this->select();
        $selectFinal->setIntegrityCheck(false);
        $selectFinal->isUseSchema(false);
        $selectFinal->from(
            ['tabela_temporaria' => new Zend_Db_Expr("($subSelect)")],
            ['*']
        );
        if($order) {
            $selectFinal->order($order);
        }

        $selectFinal->where('avaliacao_atual = 0');
        $selectFinal->where('quantidade_distribuicoes = 0');
        if ($distribuicaoAvaliacaoProposta->getIdPerfil() != Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE) {
            $selectFinal->orWhere('avaliacao_atual = 1 and quantidade_distribuicoes > 0');
        }
        return $db->fetchAll($selectFinal);
    }

    public function propostasTotal($where = array(), $order = array(), $start = null, $limit = null, $search = null)
    {
        $db = $this->getAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        $sql = $db->select()
            ->from('vwPainelAvaliarPropostas', 'count(*) as total', $this->_schema);

        foreach ($where as $coluna => $valor) {
            $sql->where($coluna, $valor);
        }

        if (!empty($search['value'])) {
            $sql->where('idProjeto like ? OR NomeProposta like ? OR Tecnico like ?', '%' . $search['value'] . '%');
        }

        $sql->order($order);

        if (!is_null($start) && $limit) {
            $start = (int)$start;
            $limit = (int)$limit;
            $sql->limitPage($start, $limit);
        }
        //echo $sql;

        return $db->fetchRow($sql);
    }
}
