<?php

class Admissibilidade_Model_DbTable_DistribuicaoAvaliacaoProposta extends MinC_Db_Table_Abstract
{
    protected $_name = "distribuicao_avaliacao_proposta";
    protected $_schema = "sac";
    protected $_primary = "id_distribuicao_avaliacao_proposta";

    const AVALIACAO_ATUAL_INATIVA = 0;
    const AVALIACAO_ATUAL_ATIVA = 1;

    public function inativarAvaliacoesProposta($id_preprojeto) {

        $this->alterar(
            ['avaliacao_atual' => Admissibilidade_Model_DbTable_DistribuicaoAvaliacaoProposta::AVALIACAO_ATUAL_INATIVA],
            ['id_preprojeto = ?' => $id_preprojeto]
        );
    }

}