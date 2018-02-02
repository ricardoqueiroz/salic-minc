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

        $selectDistribuicaoAvaliacao = '0';
        $selectQuantidadeAvaliacoes = '0';

        if (!is_null($distribuicaoAvaliacaoProposta)
            && $distribuicaoAvaliacaoProposta->getIdPerfil()
            && $distribuicaoAvaliacaoProposta->getIdOrgaoSuperior()) {
            $selectDistribuicaoAvaliacao = $this->select();
            $selectDistribuicaoAvaliacao->setIntegrityCheck(false);
            $selectDistribuicaoAvaliacao->from(
                'distribuicao_avaliacao_proposta',
                [],
                $this->getSchema('sac')
            );

            $selectDistribuicaoAvaliacao->where(
                'distribuicao_avaliacao_proposta.id_preprojeto = vwPainelAvaliarPropostas.idProjeto'
            );

            $selectDistribuicaoAvaliacao->where(
                'distribuicao_avaliacao_proposta.id_orgao_superior = ?',
                $distribuicaoAvaliacaoProposta->getIdOrgaoSuperior()
            );

            $selectDistribuicaoAvaliacao->where(
                'distribuicao_avaliacao_proposta.id_orgao_superior = vwPainelAvaliarPropostas.idSecretaria'
            );

            $selectQuantidadeAvaliacoes = clone $selectDistribuicaoAvaliacao;
            $selectQuantidadeAvaliacoes->columns(new Zend_Db_Expr('count(avaliacao_atual)'));
            $selectDistribuicaoAvaliacao->columns(new Zend_Db_Expr('avaliacao_atual'));

            $perfis = [
                $distribuicaoAvaliacaoProposta->getIdPerfil()
            ];

            if ($distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE) {
                $perfis[] = Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE;
            }

            $selectDistribuicaoAvaliacao->where(
                'distribuicao_avaliacao_proposta.id_perfil in (?)',
                $perfis
            );
        }

        $subSelect = $this->select();
        $subSelect->setIntegrityCheck(false);
        $subSelect->from('vwPainelAvaliarPropostas',
            [
                '*',
                'avaliacao_atual' => new Zend_Db_Expr("({$selectDistribuicaoAvaliacao})"),
                'quantidade_distribuicoes' => new Zend_Db_Expr("({$selectQuantidadeAvaliacoes})"),
            ],
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

        $selectFinal = $this->select();
        $selectFinal->setIntegrityCheck(false);
        $selectFinal->isUseSchema(false);
        $selectFinal->from(
            ['tabela_temporaria' => new Zend_Db_Expr("($subSelect)")],
            '*'
        );
        if($order) {
            $selectFinal->order($order);
        }

        $selectFinal->where('avaliacao_atual is null');
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
