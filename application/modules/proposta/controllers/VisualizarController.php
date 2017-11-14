<?php

class Proposta_VisualizarController extends Proposta_GenericController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
    }

    public function identificacaoAction()
    {
        $this->_helper->layout->disableLayout();

        try {
            $dados = $this->_proposta;

            if(is_array($dados)) {
                $dados['stdatafixa'] = ($dados['stdatafixa']) ? 'Sim' : 'Não';
                $dados['areaabrangencia'] = ($dados['areaabrangencia']) ? 'Sim' : 'Não';
                $dados['tpprorrogacao'] = ($dados['tpprorrogacao']) ? 'Sim' : 'Não';
                $dados['stproposta'] = ($dados['stproposta']) ? 'Sim' : 'Não';
            }

            $dados = array_map('utf8_encode', $dados);

            $this->_helper->json(array('data' => $dados, 'success' => 'true'));
        } catch (Exception $e) {
            $this->_helper->json(array('msg' => $e->getMessage(), 'success' => 'false'));
        }
    }

    public function historicoAvaliacoesAction()
    {
        $this->_helper->layout->disableLayout();

        $dados = Proposta_Model_AnalisarPropostaDAO::buscarHistorico($this->idPreProjeto);
        xd($dados);
        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function proponenteAction($idAgente)
    {
        $this->_helper->layout->disableLayout();

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function documentosAnexadosAction($idPreProjeto)
    {
        $this->_helper->layout->disableLayout();

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function localDeRealizacaoAction()
    {
        $this->_helper->layout->disableLayout();

        $arrBusca = array();
        $arrBusca['idprojeto'] = $this->idPreProjeto;
        $arrBusca['stabrangencia'] = 1;
        $tblAbrangencia = new Proposta_Model_DbTable_Abrangencia();
        $rsAbrangencia = $tblAbrangencia->buscar($arrBusca);

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function deslocamentoAction($idPreProjeto)
    {
        $this->_helper->layout->disableLayout();

        $deslocamentos = new Proposta_Model_TbDeslocamentoMapper();
        $dados = $deslocamentos->getDbTable()->buscarDeslocamento($idPreProjeto, $id);

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function planilhaOrcamentariaPropostaAction($idPreProjeto)
    {
        $this->_helper->layout->disableLayout();

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function planoDeDivulgacaoAction($idPreProjeto)
    {
        $this->_helper->layout->disableLayout();

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function planoDistribuicacaoAction($idPreProjeto)
    {
        $this->_helper->layout->disableLayout();

        $dados = [];

        $this->_helper->layout->disableLayout();

        $arrBusca = array();
        $arrBusca['idprojeto'] = $this->idPreProjeto;

        $tblAbrangencia = new Proposta_Model_DbTable_Abrangencia();
        $rsAbrangencia = $tblAbrangencia->buscar($arrBusca);
        $this->view->abrangencias = $rsAbrangencia;

        $tblPlanoDistribuicao = new PlanoDistribuicao();

        $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscar(
            array("a.idprojeto = ?" => $this->idPreProjeto, "a.stplanodistribuicaoproduto = ?" => 1),
            array("idplanodistribuicao DESC")
        );

        $this->view->planosDistribuicao = $rsPlanoDistribuicao;
        $this->abrangencias = $rsAbrangencia;

        //@todo enviar montado

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }

    public function detalhamentoPlanoDistribuicao($idPlanoDistribuicacao)
    {
        $this->_helper->layout->disableLayout();

        $dados = [];

        $this->_helper->json(array('data' => $dados, 'success' => 'true'));
    }
}
