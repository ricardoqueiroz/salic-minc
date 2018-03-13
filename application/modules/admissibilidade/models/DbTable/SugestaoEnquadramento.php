<?php

class Admissibilidade_Model_DbTable_SugestaoEnquadramento extends MinC_Db_Table_Abstract
{
    protected $_name = "sugestao_enquadramento";
    protected $_schema = "sac";
    protected $_primary = "id_sugestao_enquadramento";

    const ULTIMA_SUGESTAO_ATIVA = 1;
    const ULTIMA_SUGESTAO_INATIVA = 0;

    public function obterHistoricoEnquadramento($id_preprojeto)
    {

        $tableSelect = $this->select();
        $tableSelect->setIntegrityCheck(false);
        $tableSelect->from(
            [$this->_name => $this->_name],
            '*',
            $this->_schema
        );
        $tableSelect->joinInner(
            ['Orgaos' => 'Orgaos'],
            "{$this->_name}.id_orgao = Orgaos.org_codigo",
            [
                'org_sigla'
            ],
            $this->getSchema('tabelas')
        );
        $tableSelect->joinInner(
            ['Grupos' => 'Grupos'],
            "{$this->_name}.id_perfil_usuario = Grupos.gru_codigo",
            [
                'gru_nome'
            ],
            $this->getSchema('tabelas')
        );
        $tableSelect->joinInner(
            ['Usuarios' => 'Usuarios'],
            "{$this->_name}.id_usuario_avaliador = Usuarios.usu_codigo",
            [
                'usu_nome'
            ],
            $this->getSchema('tabelas')
        );
        $tableSelect->joinLeft(
            ['Segmento' => 'Segmento'],
            "{$this->_name}.id_segmento = Segmento.Codigo",
            [
                'segmento' => 'Descricao',
                'tp_enquadramento'
            ],
            $this->_schema
        );
        $tableSelect->joinLeft(
            ['Area' => 'Area'],
            "{$this->_name}.id_area = Area.Codigo",
            [
                'area' => 'Descricao'
            ],
            $this->_schema
        );

        $tableSelect->where('id_preprojeto = ?', $id_preprojeto);
        $tableSelect->order('data_avaliacao desc');

        $resultado = $this->fetchAll($tableSelect);
        if ($resultado) {
            return $resultado->toArray();
        }
    }

    /**
     * @param Admissibilidade_Model_SugestaoEnquadramento $sugestaoEnquadramento
     * @return bool|null
     */
    public function isPropostaEnquadrada(Admissibilidade_Model_SugestaoEnquadramento $sugestaoEnquadramento)
    {
        $arrayPesquisa = [];
        if ($sugestaoEnquadramento->getIdDistribuicaoAvaliacaoProposta()) {
            $arrayPesquisa['id_distribuicao_avaliacao_proposta'] = $sugestaoEnquadramento->getIdDistribuicaoAvaliacaoProposta();
        }
        if ($sugestaoEnquadramento->getIdPreprojeto()
            && $sugestaoEnquadramento->getIdOrgao()
            && $sugestaoEnquadramento->getIdPerfilUsuario()) {
            $arrayPesquisa['id_preprojeto'] = $sugestaoEnquadramento->getIdPreprojeto();
            $arrayPesquisa['id_orgao'] = $sugestaoEnquadramento->getIdOrgao();
            $arrayPesquisa['id_perfil_usuario'] = $sugestaoEnquadramento->getIdPerfilUsuario();
        }

        if (count($arrayPesquisa) > 0) {
            $resultado = $this->findAll($arrayPesquisa);
            if (count($resultado) > 0) {
                return true;
            }
            return false;
        }
    }


    public function inativarSugestoes($id_preprojeto)
    {
        $this->alterar(
            ['ultima_sugestao' => self::ULTIMA_SUGESTAO_INATIVA],
            ['id_preprojeto = ?' => $id_preprojeto]
        );
    }

    public function obterSugestaoAtiva($id_preprojeto)
    {
        return $this->findBy(
            [
                'id_preprojeto' => $id_preprojeto,
                'ultima_sugestao' => self::ULTIMA_SUGESTAO_ATIVA,
            ]
        );
    }

    public function salvarSugestaoEnquadramento(array $dadosSugestaoEnquadramento, $id_preprojeto)
    {
        $sugestaoEnquadramento = new Admissibilidade_Model_SugestaoEnquadramento();

        $descricao_motivacao = trim($dadosSugestaoEnquadramento['descricao_motivacao']);
        if (empty($descricao_motivacao)) {
            throw new Exception("O campo 'Parecer de Enquadramento' é de preenchimento obrigatório.");
        }

        if ($dadosSugestaoEnquadramento['id_perfil'] != Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE
            && !$sugestaoEnquadramento->isPermitidoSugerirEnquadramento($dadosSugestaoEnquadramento['id_perfil'])) {
            throw new Exception("Perfil sem permissão para executar a ação");
        }

        $id_area = ($dadosSugestaoEnquadramento['id_area']) ? $dadosSugestaoEnquadramento['id_area'] : null;
        $id_segmento = ($dadosSugestaoEnquadramento['id_segmento']) ? $dadosSugestaoEnquadramento['id_segmento'] : null;
        $sugestaoEnquadramentoDbTable = new Admissibilidade_Model_DbTable_SugestaoEnquadramento();

        $orgaoDbTable = new Orgaos();
        $resultadoOrgaoSuperior = $orgaoDbTable->codigoOrgaoSuperior($dadosSugestaoEnquadramento['id_orgao']);
        $orgaoSuperior = $resultadoOrgaoSuperior[0]['Superior'];

        $distribuicaoAvaliacaoPropostaDtTable = new Admissibilidade_Model_DbTable_DistribuicaoAvaliacaoProposta();
        $distribuicaoAvaliacaoProposta = $distribuicaoAvaliacaoPropostaDtTable->findBy([
            'id_preprojeto' => $id_preprojeto,
            'id_orgao_superior' => $orgaoSuperior,
            'id_perfil' => $dadosSugestaoEnquadramento['id_perfil']
        ]);

        if (!$distribuicaoAvaliacaoProposta &&
            (
                $dadosSugestaoEnquadramento['id_perfil'] != Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE
                && $dadosSugestaoEnquadramento['id_perfil'] != Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE
            )
        ) {
            throw new Exception("Distribui&ccedil;&atilde;o n&atilde;o localizada para o perfil atual.");
        }

        $dadosNovaSugestaoEnquadramento = [
            'id_orgao' => $dadosSugestaoEnquadramento['id_orgao'],
            'id_preprojeto' => $id_preprojeto,
            'id_orgao_superior' => $orgaoSuperior,
            'id_perfil_usuario' => $dadosSugestaoEnquadramento['id_perfil'],
            'id_usuario_avaliador' => $dadosSugestaoEnquadramento['id_usuario_avaliador'],
            'id_area' => $id_area,
            'id_segmento' => $id_segmento,
            'descricao_motivacao' => $descricao_motivacao,
            'data_avaliacao' => $sugestaoEnquadramentoDbTable->getExpressionDate(),
            'ultima_sugestao' => Admissibilidade_Model_DbTable_SugestaoEnquadramento::ULTIMA_SUGESTAO_ATIVA,
        ];

        $dadosBuscaPorSugestao = $sugestaoEnquadramentoDbTable->findBy(
            [
                'id_orgao' => $dadosSugestaoEnquadramento['id_orgao'],
                'id_preprojeto' => $id_preprojeto,
                'id_orgao_superior' => $orgaoSuperior,
                'id_perfil_usuario' => $dadosSugestaoEnquadramento['id_perfil'],
                'id_usuario_avaliador' => $dadosSugestaoEnquadramento['id_usuario_avaliador']
            ]
        );


        if ($distribuicaoAvaliacaoProposta && $distribuicaoAvaliacaoProposta['id_distribuicao_avaliacao_prop']) {
            $dadosNovaSugestaoEnquadramento['id_distribuicao_avaliacao_proposta'] = $distribuicaoAvaliacaoProposta['id_distribuicao_avaliacao_prop'];
        }
        if (count($dadosBuscaPorSugestao) < 1) {
            $sugestaoEnquadramentoDbTable->inativarSugestoes($id_preprojeto);
            $sugestaoEnquadramentoDbTable->inserir($dadosNovaSugestaoEnquadramento);
        } else {
            $dadosBuscaPorSugestao['id_distribuicao_avaliacao_proposta'] = $distribuicaoAvaliacaoProposta['id_distribuicao_avaliacao_prop'];
            $sugestaoEnquadramentoDbTable->update($dadosNovaSugestaoEnquadramento, [
                'id_sugestao_enquadramento = ?' => $dadosBuscaPorSugestao['id_sugestao_enquadramento']
            ]);
        }
    }

}