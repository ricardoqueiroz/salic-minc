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

        $select = $this->obterQueryPropostasParaAvaliacao(
            $where,
            $order,
            $start,
            $limit,
            $search,
            $distribuicaoAvaliacaoProposta
        );

        return $db->fetchAll($select);
    }

    public function obterQuantidadePropostasParaAvaliacao(
        $where = [],
        $order = [],
        $start = 0,
        $limit = 10,
        $search = null,
        Admissibilidade_Model_DistribuicaoAvaliacaoProposta $distribuicaoAvaliacaoProposta = null
    )
    {
        $subSelectPropostaParaAvaliacao = $this->obterQueryPropostasParaAvaliacao(
            $where,
            $order,
            $start,
            $limit,
            $search,
            $distribuicaoAvaliacaoProposta
        );

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->isUseSchema(false);
        $select->from(
            ['total_propostas_para_avaliacao' => new Zend_Db_Expr("({$subSelectPropostaParaAvaliacao})")],
            ['total' => new Zend_Db_Expr('count(*)')]
        );

        $db = $this->getAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchRow($select);
    }

    private function obterQueryPropostasParaAvaliacao(
        $where = [],
        $order = [],
        $start = 0,
        $limit = 10,
        $search = null,
        Admissibilidade_Model_DistribuicaoAvaliacaoProposta $distribuicaoAvaliacaoProposta = null
    )
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from('vwPainelAvaliarPropostas',
            ['*'],
            $this->_schema);

        if (!is_null($start) && $limit) {
            $start = (int)$start;
            $limit = (int)$limit;
            $select->limitPage($start, $limit);
        }

        $select->joinLeft(
            ['distribuicao_avaliacao_proposta']
            , "distribuicao_avaliacao_proposta.id_preprojeto = vwPainelAvaliarPropostas.idProjeto
                    
                    and distribuicao_avaliacao_proposta.id_orgao_superior = {$distribuicaoAvaliacaoProposta->getIdOrgaoSuperior()}"
            ,
            [
                'avaliacao_atual' => "coalesce(distribuicao_avaliacao_proposta.avaliacao_atual, '0')",
                'quantidade_distribuicoes' => "coalesce(distribuicao_avaliacao_proposta.id_distribuicao_avaliacao_proposta, '0')",
                'dias_corridos_distribuicao' => new Zend_Db_Expr('DATEDIFF(d, distribuicao_avaliacao_proposta.data_distribuicao, GETDATE())')
            ]
            , $this->getSchema('sac')
        );

        $select->joinLeft(
            ['sugestao_enquadramento']
            , "sugestao_enquadramento.id_preprojeto = vwPainelAvaliarPropostas.idProjeto
                    and sugestao_enquadramento.id_orgao_superior = {$distribuicaoAvaliacaoProposta->getIdOrgaoSuperior()}
                    and sugestao_enquadramento.id_perfil_usuario = {$distribuicaoAvaliacaoProposta->getIdPerfil()}"
            , [
                'sugestao_enquadramento.id_area',
                'sugestao_enquadramento.id_sugestao_enquadramento',
            ]
            , $this->getSchema('sac')
        );
        if ($distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE
            || $distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COMPONENTE_COMISSAO) {

            if ($distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COMPONENTE_COMISSAO) {

                $select->isUseSchema(false);
                $selectPenultimaDistribuicao = $this->obterQueryPenultimaSugestaoEnquadramento();
                $select->joinLeft(
                    ['sugestao_distribuida' => 'sugestao_enquadramento']
                    , "sugestao_distribuida.id_preprojeto = vwPainelAvaliarPropostas.idProjeto
                            and sugestao_distribuida.id_sugestao_enquadramento = ({$selectPenultimaDistribuicao})"
                    , []
                    , $this->getSchema('sac')
                );

                $select->isUseSchema(true);

                $auth = Zend_Auth::getInstance();
                $tblAgente = new Agente_Model_DbTable_Agentes();
                $rsAgente = $tblAgente->buscarAgenteENome(
                    ['CNPJCPF = ?' => $auth->getIdentity()->usu_identificacao]
                );
                if ($rsAgente && count($rsAgente->current()->toArray()) > 0) {
                    $select->joinLeft(
                        ['tbtitulacaoconselheiro']
                        , "
                        tbtitulacaoconselheiro.cdArea = sugestao_distribuida.id_area
                        and tbtitulacaoconselheiro.stTitular = 1
                        and tbtitulacaoconselheiro.stConselheiro = 'A'
                    "
                        , []
                        , $this->getSchema('agentes')
                    );
                    $agente = $rsAgente->current()->toArray();
                    $select->where('tbtitulacaoconselheiro.idAgente = ?', $agente['idAgente']);
                }
            }
        }

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if (!empty($search['value'])) {
            $select->where('idProjeto like ? OR NomeProposta like ? OR Tecnico like ?', "%{$search['value']}%");
        }

        $restricaoPropostasParaAvaliacao = $this->obterRestricaoPropostasParaAvaliacao($distribuicaoAvaliacaoProposta);
        if ($restricaoPropostasParaAvaliacao) {
            $select->where($restricaoPropostasParaAvaliacao);
        }

        if ($order) {
            $select->order($order);
        }
//xdnb($select->assemble());
        return $select;
    }

    private function obterQueryPenultimaSugestaoEnquadramento()
    {
        $selectPenultimaDistribuicao = $this->select();
        $selectPenultimaDistribuicao->setIntegrityCheck(false);
        $selectPenultimaDistribuicao->from(
            ['sub_select_sugestao_enquadramento' => 'sugestao_enquadramento'],
            [new Zend_Db_Expr('id_sugestao_enquadramento')],
            $this->getSchema('sac')
        );
        $selectPenultimaDistribuicao->limit(1);
        $selectPenultimaDistribuicao->order('data_avaliacao desc');
        $selectPenultimaDistribuicao->where('id_preprojeto = vwPainelAvaliarPropostas.idProjeto');
        $selectPenultimaDistribuicao->where(
            'sub_select_sugestao_enquadramento.id_distribuicao_avaliacao_proposta 
                   <> distribuicao_avaliacao_proposta.id_distribuicao_avaliacao_proposta
               OR sub_select_sugestao_enquadramento.id_distribuicao_avaliacao_proposta is null'
        );

        return $selectPenultimaDistribuicao;
    }

    private function obterRestricaoPropostasParaAvaliacao(Admissibilidade_Model_DistribuicaoAvaliacaoProposta $distribuicaoAvaliacaoProposta)
    {
        if ($distribuicaoAvaliacaoProposta->getIdPerfil()) {
            $restricaoPropostasParaAvaliacao = '( ';
            if ($distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE
                || $distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE) {
                $restricaoPropostasParaAvaliacao .= ' distribuicao_avaliacao_proposta.avaliacao_atual is null ';
                $restricaoPropostasParaAvaliacao .= ' AND distribuicao_avaliacao_proposta.id_distribuicao_avaliacao_proposta is null ';
//                $restricaoPropostasParaAvaliacao .= ' AND sugestao_enquadramento.id_area is null';
//                $restricaoPropostasParaAvaliacao .= ' AND sugestao_enquadramento.id_sugestao_enquadramento is null ';
            }
            if ($distribuicaoAvaliacaoProposta->getIdPerfil() != Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE) {
                if (!empty($restricaoPropostasParaAvaliacao) && $restricaoPropostasParaAvaliacao != '( ') {
                    $restricaoPropostasParaAvaliacao .= ' OR ';
                }

                $restricaoPropostasParaAvaliacao .= ' distribuicao_avaliacao_proposta.avaliacao_atual = 1';
                $restricaoPropostasParaAvaliacao .= ' AND distribuicao_avaliacao_proposta.id_distribuicao_avaliacao_proposta > 0 ';
                $perfisDistribuicao = $this->obterPerfisDistribuicao($distribuicaoAvaliacaoProposta);
                if ($perfisDistribuicao) {
                    $restricaoPropostasParaAvaliacao .= " AND distribuicao_avaliacao_proposta.id_perfil IN ({$perfisDistribuicao})";
                }
            }
            $restricaoPropostasParaAvaliacao .= ' )';
            return $restricaoPropostasParaAvaliacao;
        }
    }


    private function obterPerfisDistribuicao(Admissibilidade_Model_DistribuicaoAvaliacaoProposta $distribuicaoAvaliacaoProposta)
    {
        if ($distribuicaoAvaliacaoProposta->getIdPerfil() != Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE) {
            $perfis = [
                $distribuicaoAvaliacaoProposta->getIdPerfil()
            ];

            if ($distribuicaoAvaliacaoProposta->getIdPerfil() == Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE) {
                $perfis[] = Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE;
            }
            return implode(',', $perfis);
        }
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

        return $db->fetchRow($sql);
    }
}
