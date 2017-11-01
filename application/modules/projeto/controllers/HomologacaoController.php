<?php
class Projeto_HomologacaoController extends Proposta_GenericController {

    private $arrBreadCrumb = [];

    public function init()
    {
        $auth = Zend_Auth::getInstance(); // pega a autenticacao
        $idPreProjeto = $this->getRequest()->getParam('idPreProjeto');

        $arrIdentity = array_change_key_case((array) Zend_Auth::getInstance()->getIdentity());
        $GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo');

	    // verifica as permissoes
        //$PermissoesGrupo = array();
        //$PermissoesGrupo[] = 97;  // Gestor do SALIC
        //$PermissoesGrupo[] = 93;  // Coordenador de Parecerista
        //if (isset($arrIdentity['usu_codigo'])) {
            //parent::perfil(1, $PermissoesGrupo);
        //} else {
            //parent::perfil(4, $PermissoesGrupo);
        //}

        /*********************************************************************************************************/

        $cpf = isset($arrIdentity['usu_codigo']) ? $arrIdentity['usu_identificacao'] : $arrIdentity['cpf'];

        // Busca na SGCAcesso
        $modelSgcAcesso 	 = new Autenticacao_Model_Sgcacesso();
        $arrAcesso = $modelSgcAcesso->findBy(array('cpf' => $cpf));

        // Busca na Usuarios
        //Excluir ProposteExcluir Proposto
        $usuarioDAO   = new Autenticacao_Model_Usuario();
        $arrUsuario = $usuarioDAO->findBy(array('usu_identificacao' => $cpf));

        // Busca na Agentes
        $tableAgentes  = new Agente_Model_DbTable_Agentes();
        $arrAgente = $tableAgentes->findBy(array('cnpjcpf' => trim($cpf)));

        if ($arrAcesso)  $this->idResponsavel = $arrAcesso['idusuario'];
        if ($arrAgente)  $this->idAgente 	  = $arrAgente['idagente'];
        if ($arrUsuario) $this->idUsuario     = $arrUsuario['usu_codigo'];
        if ($this->idAgente != 0) $this->usuarioProponente = "S";
        $this->cpfLogado = $cpf;


        $this->arrBreadCrumb[] = array('url' => '/principal', 'title' => 'In&iacute;cio', 'description' => 'Ir para in&iacute;cio');
        parent::init();
    }

    public function indexAction() {
        $this->arrBreadCrumb[] = array('url' => '', 'title' => 'Homologacao de Projetos', 'description' => 'Tela atual');
        $this->view->arrBreadCrumb = $this->arrBreadCrumb;
    }

    public function listarAction()
    {
        $dbTable = new Projeto_Model_DbTable_VwPainelDeHomologacaoDeProjetos();
        $this->_helper->layout->disableLayout();
        $this->view->arrResult = $dbTable->findAll();
    }

    public function visualizarAction()
    {
        $this->_helper->layout->disableLayout();
        self::prepareData($this->getRequest()->getParam('id'));
    }

    public function homologarAction()
    {
        $this->_helper->layout->disableLayout();

        if ($this->getRequest()->isPost()) {
            $this->_helper->viewRenderer->setNoRender(true);
            $mapper = new Projeto_Model_TbHomologacao();
//            $this->_helper->json(array('status' => $mapper->responder($this->getRequest()->getPost()), 'msg' => $mapper->getMessages(), 'redirect' => $strUrl));
        } else {
            self::prepareData($this->getRequest()->getParam('id'));
            $this->prepareForm(array(
                'idDestinatario' => array('disabled' => true),
                'idDestinatarioUnidade' => array('disabled' => true),
                'dsMensagem' => array('disabled' => true),
            ), '', $strActionBack);
            if ($this->arrProjeto) {
                $this->arrBreadCrumb[] = array('url' => '/admissibilidade/enquadramento/listar', 'title' => 'Enquadramentos', 'description' => 'Ir para a tela de enquadramentos');
                $arrBreadCrumb[] = array('url' => '', 'title' => "Perguntas: {$this->arrProjeto['AnoProjeto']}{$this->arrProjeto['Sequencial']} - {$this->arrProjeto['NomeProjeto']}", 'description' => 'Ir para a tela de perguntas');
            } else {
                $this->arrBreadCrumb[] = array('url' => '/admissibilidade/mensagem/index', 'title' => 'Perguntas', 'description' => 'Ir para a tela de perguntas');
            }
            $this->arrBreadCrumb[] = array('url' => '', 'title' => 'Responder pergunta', 'description' => 'Tela atual');
            $this->view->arrBreadCrumb = $this->arrBreadCrumb;
        }
    }

    public function parecerAction()
    {
        $this->_helper->layout->disableLayout();
    }

    private function prepareData($intIdPronac)
    {
        # PARTE 1
        $dbTablePainelHomologacao = new Projeto_Model_DbTable_VwPainelDeHomologacaoDeProjetos();
        $dbTableEnquadramentoProjeto = new Projeto_Model_DbTable_VwEnquadramentoDoProjeto();
        # PARTE 2 # PARTE 4
        $dbTableParecer = new Parecer();
        # PARTE 3
        $dbTableAcaoProjeto = new tbAcaoAlcanceProjeto();
        # PARTE 5
        $dbTableHomologacao = new Projeto_Model_DbTable_TbHomologacao();
        $arrValue = $dbTablePainelHomologacao->findBy($intIdPronac);
        $arrValue['enquadramentoProjeto'] = $dbTableEnquadramentoProjeto->findBy(17896);
        $arrValue['parecer'] = $dbTableParecer->findBy(17896);
        $arrValue['acaoProjeto'] = $dbTableAcaoProjeto->findBy(['tpAnalise' => '1', 'idPronac' => 201495]); # 3
        $arrValue['aparicaoComissario'] = $dbTableParecer->findBy(['TipoParecer' => '1', 'stAtivo' => '1', 'idTipoAgente' => '6', 'IdPRONAC' => 131182]); # 4
        $arrValue['parecerHomologacao'] = $dbTableHomologacao->findBy(['idPronac' => $intIdPronac, 'tpHomologacao' => '1']); # 5
        $this->view->arrValue = $arrValue;
    }
}
