<?php

class PrestacaoContas_PrestacaoContasController extends  MinC_Controller_Action_Abstract
{
    public function init()
    {
        $PermissoesGrupo = [
            Autenticacao_Model_Grupos::TECNICO_PRESTACAO_DE_CONTAS,
            Autenticacao_Model_Grupos::COORDENADOR_PRESTACAO_DE_CONTAS
        ];

        $auth = Zend_Auth::getInstance();

        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);

        isset($auth->getIdentity()->usu_codigo) ? $this->idUsuario = $auth->getIdentity()->usu_codigo : $this->idUsuario = $auth->getIdentity()->IdUsuario;

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');

        if (isset($auth->getIdentity()->usu_codigo)) {

            $this->codGrupo = $GrupoAtivo->codGrupo;
            $this->codOrgao = $GrupoAtivo->codOrgao;
            $this->codOrgaoSuperior = (!empty($auth->getIdentity()->usu_org_max_superior)) ? $auth->getIdentity()->usu_org_max_superior : null;
        }

        parent::init();
    }

    public function indexAction()
    {
    }

    public function tipoAvaliacaoAction()
    {
        $idPronac = $this->_request->getParam("idPronac");
        if (!$idPronac) {
           throw new Exception('Não existe idPronac');
        }

        $this->view->idPronac = $this->_request->getParam("idPronac");
    }

    /* @todo: adicionar função de salvar o tipo de amostragem*/
    public function tipoAvaliacaoSalvarAction()
    {
        $idPronac = $this->_request->getParam("idPronac");
        $avaliacao = $this->_request->getParam("avaliacao");

        if (!$idPronac) {
           throw new Exception('Não existe idPronac');
        }

        if (!$avaliacao) {
           throw new Exception('Não existe avaliacao');
        }

        if ($avaliacao == "todos") {
            $this->redirect('/realizarprestacaodecontas/planilhaorcamentaria/idPronac/' . $idPronac );
        }

        $this->redirect('/prestacao-contas/prestacao-contas/amostragem/idPronac/' . $idPronac );

        /* $this->view->idPronac = $this->_request->getParam("idPronac"); */
    }

    public function amostragemAction()
    {
        $idPronac = $this->_request->getParam("idPronac");
        if (!$idPronac) {
           throw new Exception('Não existe idPronac');
        }
        $idPronac = $this->_request->getParam("idPronac");

        $this->view->idPronac = $this->_request->getParam("idPronac");
    }
}
