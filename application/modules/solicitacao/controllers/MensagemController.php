<?php

class Solicitacao_MensagemController extends Solicitacao_GenericController
{

    public function init()
    {
        parent::init();

        if (!empty($this->idPreProjeto) || !empty($this->idPronac)) {
            parent::verificarPermissaoAcesso(!empty($this->idPreProjeto), !empty($this->idPronac), false);
        }
    }

    public function indexAction()
    {
    }

    /**
     * Metodo responsavel por preparar o formulario conforme cada acao.
     *
     * @name prepareForm
     * @param array $arrConfig
     *
     */
    public function prepareForm($dataForm = [], $arrConfig = [], $strUrlAction = '', $strActionBack = 'index')
    {

        $intId = $this->getRequest()->getParam('id', null);

        if (!empty($dataForm['idDocumento'])) {
            $tbl = new Arquivo_Model_DbTable_TbDocumento();
            $dataForm['arquivo'] = $tbl->buscarDocumento($dataForm['idDocumento'])->toArray();
        }
        if ($this->proposta) {
            $dataForm['idProjeto'] = $this->idPreProjeto;
            $dataForm['NomeProjeto'] = isset($this->proposta->NomeProjeto) ? $this->proposta->NomeProjeto : '';
        }

        if ($this->projeto) {
            $dataForm['Pronac'] = $this->projeto->AnoProjeto . $this->projeto->Sequencial;
            $dataForm['NomeProjeto'] = isset($this->projeto->NomeProjeto) ? $this->projeto->NomeProjeto : '';
            $dataForm['idPronac'] = $this->idPronac;
        }

        $this->view->arrPartial = array(
            'dataForm' => $dataForm,
            'arrConfig' => $arrConfig,
            'id' => $intId,
            'urlAction' => $strUrlAction,
            'strActionBack' => $strActionBack,
            'currentUrl' => Zend_Controller_Front::getInstance()->getRequest()->getRequestUri(),
            'ehProponente' => $this->ehProponente
        );
    }

    public function listarAction()
    {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->getRequest()->getParam('idPronac', null);
        $idPreProjeto = $this->getRequest()->getParam('idPreProjeto', null);
        $this->view->ehTecnico = false;

        $vwSolicitacoes = new Solicitacao_Model_vwPainelDeSolicitacaoProponente();

        $where = [];
        if ($idPronac) {
            $where['idPronac = ?'] = $idPronac;
        }

        if ($idPreProjeto) {
            $where['idProjeto = ?'] = $idPreProjeto;
        }

        # Proponente
        if (isset($this->usuario['cpf'])) {
            $where["(idAgente = {$this->idAgente} OR idSolicitante = {$this->idUsuario})"] = '';
        }

        if (isset($this->usuario['usu_codigo'])) {

            if (isset($this->grupoAtivo->codOrgao)) {

                $grupos = new Autenticacao_Model_Grupos();
                $tecnicos = $grupos->buscarTecnicosPorOrgao($this->grupoAtivo->codOrgao)->toArray();

                if (in_array($this->grupoAtivo->codGrupo, array_column($tecnicos, 'gru_codigo'))) {
                    $where['idTecnico = ?'] = $this->idUsuario;
                }

                $where['idOrgao = ?'] = $this->grupoAtivo->codOrgao;

            }

            if (isset($this->grupoAtivo->codOrgao)) {
                $where['idOrgao = ?'] = $this->grupoAtivo->codOrgao;
            }

            $where['siEncaminhamento = ?'] = Solicitacao_Model_TbSolicitacao::SOLICITACAO_ENCAMINHADA_AO_MINC;
        }

        $solicitacoes = $vwSolicitacoes->buscar($where);

        $this->view->arrResult = $solicitacoes;
        $this->view->idPronac = $idPronac;

    }

    /**
     * utilizado nas notificacoes
     */
    public function listarAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->getRequest()->getParam('idPronac', null);
        $idPreProjeto = $this->getRequest()->getParam('idPreProjeto', null);
        $this->view->ehTecnico = false;

        $vwSolicitacoes = new Solicitacao_Model_vwPainelDeSolicitacaoProponente();

        $where = [];
        if ($idPronac) {
            $where['idPronac = ?'] = $idPronac;
        }

        if ($idPreProjeto) {
            $where['idProjeto = ?'] = $idPreProjeto;
        }

        # Proponente
        if (isset($this->usuario['cpf'])) {
            $where["(idAgente = {$this->idAgente} OR idSolicitante = {$this->idUsuario})"] = '';
            $where['siEncaminhamento = ?'] = Solicitacao_Model_TbSolicitacao::SOLICITACAO_FINALIZADA_MINC;
            $where['stLeitura = ?'] = 0;
        }

        if (isset($this->usuario['usu_codigo'])) {

            $where['idTecnico = ?'] = $this->idUsuario;
            $where['dsResposta IS NULL'] = '';

            if (isset($this->grupoAtivo->codOrgao)) {
                $where['idOrgao = ?'] = $this->grupoAtivo->codOrgao;
            }

            $where['siEncaminhamento = ?'] = Solicitacao_Model_TbSolicitacao::SOLICITACAO_ENCAMINHADA_AO_MINC;
        }


        $solicitacoes = $vwSolicitacoes->buscar($where, ['dtResposta DESC', 'dtSolicitacao DESC']);

        $this->view->arrResult = $solicitacoes;
        $this->view->idPronac = $idPronac;
    }

    public function visualizarAction()
    {

        $urlAction = $this->_urlPadrao . "/solicitacao/mensagem/salvar";

        try {

            $idSolicitacao = $this->getRequest()->getParam('id', null);

            if (empty($idSolicitacao))
                throw new Exception("Informe o id da solicita&ccedil;&atilde;o para visualizar!");

            $where['idSolicitacao = ?'] = $idSolicitacao;

            $vwSolicitacao = new Solicitacao_Model_vwPainelDeSolicitacaoProponente();
            $dataForm = $vwSolicitacao->buscar($where)->current()->toArray();

            if (empty($dataForm))
                throw new Exception("Nenhuma solicita&ccedil;&atilde;o encontrada!");

            $permissao = parent::verificarPermissaoAcesso($dataForm['idProjeto'], $dataForm['idPronac'], false, true);

            if ($permissao['status'] === false)
                throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esta solicita&ccedil;&atilde;o");

            # marcar como mensagem lida pelo proponente
            if ($dataForm['siEncaminhamento'] == Solicitacao_Model_TbSolicitacao::SOLICITACAO_FINALIZADA_MINC) {
                if ($dataForm['idAgente'] == $this->idAgente || $dataForm['idSolicitante'] == $this->idUsuario) {

                    $model = new Solicitacao_Model_TbSolicitacao();
                    $model->setIdSolicitacao($dataForm['idSolicitacao']);
                    $model->setStLeitura(1);
                    $mapperSolicitacao = new Solicitacao_Model_TbSolicitacaoMapper();
                    $mapperSolicitacao->atualizarSolicitacao($model);

                }
            }

            $arrConfig['dsResposta']['show'] = true;

            self::prepareForm($dataForm, $arrConfig, $urlAction);

        } catch (Exception $objException) {
            parent::message($objException->getMessage(), "/solicitacao/mensagem", "ALERT");
        }
    }


    public function solicitarAction()
    {
        $urlAction = $this->_urlPadrao . "/solicitacao/mensagem/salvar";
        $urlCallBack = $this->_urlPadrao . "/solicitacao/mensagem/index";

        try {

            if (empty($this->idPronac) && empty($this->idPreProjeto))
                throw new Exception("Informe o projeto ou proposta para realizar uma solicita&ccedil;&atilde;o");

            $dataForm = [
                'siEncaminhamento' => Solicitacao_Model_TbSolicitacao::SOLICITACAO_CADASTRADA
            ];

            $arrConfig['dsSolicitacao']['disabled'] = false;
            $arrConfig['actions']['show'] = true;

            if ($this->projeto) {
                $urlCallBack .= '/idPronac/' . $this->idPronac;
                $dataForm['idPronac'] = $this->idPronac;
            } else if ($this->proposta) {
                $urlCallBack .= '/idPreProjeto/' . $this->idPreProjeto;
                $dataForm['idProjeto'] = $this->idPreProjeto;
            }

            $mapperSolicitacao = new Solicitacao_Model_TbSolicitacaoMapper();
            $dataForm = $mapperSolicitacao->existeSolicitacaoNaoRespondida($dataForm);

            if ($dataForm['siEncaminhamento'] == Solicitacao_Model_TbSolicitacao::SOLICITACAO_ENCAMINHADA_AO_MINC) {
                $this->redirect($this->_urlPadrao . '/solicitacao/mensagem/visualizar/id/' . $dataForm['idSolicitacao']);
            }

            self::prepareForm($dataForm, $arrConfig, $urlAction, $urlCallBack);


        } catch (Exception $objException) {
            parent::message($objException->getMessage(), $urlCallBack, "ALERT");
        }
    }

    public function salvarAction()
    {
        $status = false;

        if ($this->getRequest()->isPost()) {

            try {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);

                $arrayForm = $this->getRequest()->getPost();

                $strUrl = '/solicitacao/mensagem/index';
                $strParams = '';
                $strParams .= ($arrayForm['idPronac']) ? '/idPronac/' . $arrayForm['idPronac'] : '';
                $strParams .= ($arrayForm['idProjeto']) ? '/idPreProjeto/' . $arrayForm['idProjeto'] : '';
                $strUrl = $strUrl . $strParams;
                $arrayForm['idUsuario'] = $this->idUsuario;

                $mapperSolicitacao = new Solicitacao_Model_TbSolicitacaoMapper();
                $solicitacao = $mapperSolicitacao->existeSolicitacaoNaoRespondida($arrayForm);

                if (isset($solicitacao['siEncaminhamento'])) {

                    if ($solicitacao['siEncaminhamento'] == Solicitacao_Model_TbSolicitacao::SOLICITACAO_ENCAMINHADA_AO_MINC) {
                        throw new Exception("Voc&ecirc; j&aacute; possui uma solicita&ccedil;&atilde;o aguardando resposta para este projeto!");
                    }

                    $arrayForm['idSolicitacao'] = $solicitacao['idSolicitacao'];
                }

                $mapperSolicitacao = new Solicitacao_Model_TbSolicitacaoMapper();
                $idSolicitacao = $mapperSolicitacao->salvar($arrayForm);

                if ($idSolicitacao) {
                    $strUrl = '/solicitacao/mensagem/visualizar/id/' . $idSolicitacao . $strParams;
                    $status = true;
                }

                $this->_helper->json(array('status' => $status, 'msg' => $mapperSolicitacao->getMessages(), 'redirect' => $strUrl));
            } catch (Exception $objException) {
                $this->_helper->json(array('status' => false, 'msg' => $objException->getMessage(), 'redirect' => $strUrl));
            }

        }
    }


    public function responderAction()
    {
        $idSolicitacao = $this->getRequest()->getParam('id', null);
        $strActionBack = "solicitacao/mensagem/index";

        try {

            if (empty($idSolicitacao))
                throw new Exception("Informe o id da solicita&ccedil;&atilde;o para responder!");

            $where['idSolicitacao = ?'] = $idSolicitacao;

            $vwSolicitacao = new Solicitacao_Model_vwPainelDeSolicitacaoProponente();
            $solicitacao = $vwSolicitacao->buscar($where)->current()->toArray();

            if (empty($solicitacao))
                throw new Exception("Nenhuma solicita&ccedil;&atilde;o encontrada!");

            if ($solicitacao['idTecnico'] != $this->idUsuario)
                throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o para responder esta solicita&ccedil;&atilde;o!");

            if (!empty($solicitacao['dsResposta']))
                $this->redirect("/solicitacao/mensagem/visualizar/id/{$idSolicitacao}");


            if ($this->getRequest()->isPost()) {

                $status = false;

                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                $arrayForm = $this->getRequest()->getPost();

                $strUrl = '/solicitacao/mensagem/index';
                $strUrl .= ($arrayForm['idPronac']) ? '/idPronac/' . $arrayForm['idPronac'] : '';
                $strUrl .= ($arrayForm['idProposta']) ? '/idproposta/' . $arrayForm['idproposta'] : '';
                $mapperSolicitacao = new Solicitacao_Model_TbSolicitacaoMapper();

                $idSolicitacao = $mapperSolicitacao->responder($this->getRequest()->getPost());

                if ($idSolicitacao) {
                    $strUrl = '/solicitacao/mensagem/visualizar/id/' . $idSolicitacao;
                    $status = true;
                }

                $this->_helper->json(
                    array(
                        'status' => $status,
                        'msg' => $mapperSolicitacao->getMessages(),
                        'redirect' => $strUrl
                    )
                );

            } else {

                $vwGrupos = new vwUsuariosOrgaosGrupos();

                $arrConfig = [
                    'dsSolicitacao' => ['disabled' => true],
                    'dsResposta' => ['show' => true, 'disabled' => false],
                    'actions' => ['show' => true],
                    'actionredistribuirSolicitacao' => $this->_urlPadrao . "/solicitacao/mensagem/redistribuir-solicitacao",
                    'redistribuirTecnicos' => $vwGrupos->carregarTecnicosPorUnidade($solicitacao['idOrgao'])
                ];


                self::prepareForm($solicitacao, $arrConfig, '', $strActionBack);
            }

            $this->view->arrConfig['dsMensagem'] = ['disabled' => true];

        } catch (Exception $objException) {
            parent::message($objException->getMessage(), $strActionBack, "ALERT");
        }
    }

    public function redistribuirSolicitacaoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {

            try {

                $arrayForm = $this->getRequest()->getPost();

                if (empty($arrayForm['idTecnico']))
                    throw new Exception("T&eacute;cnico &eacute; obrigat&oacute;rio!");

                if (empty($arrayForm['idSolicitacao']))
                    throw new Exception("Solicita&ccedil;&atilde;o &eacute; obrigat&oacute;rio!");

                $strUrl = '/solicitacao/mensagem/index';
                $strUrl .= ($arrayForm['idPronac']) ? '/idPronac/' . $arrayForm['idPronac'] : '';
                $strUrl .= ($arrayForm['idProposta']) ? '/idproposta/' . $arrayForm['idproposta'] : '';

                $model = new Solicitacao_Model_TbSolicitacao();
                $model->setIdSolicitacao($arrayForm['idSolicitacao']);
                $model->setIdTecnico($arrayForm['idTecnico']);

                $mapperSolicitacao = new Solicitacao_Model_TbSolicitacaoMapper();
                $idSolicitacao = $mapperSolicitacao->atualizarSolicitacao($model);

                if ($idSolicitacao) {
                    $this->_helper->json(array('status' => true, 'msg' => 'Encaminhamento realizado com sucesso!', 'redirect' => $strUrl));
                }

            } catch (Exception $objException) {
                $this->_helper->json(array('status' => false, 'msg' => $objException->getMessage(), 'redirect' => $strUrl));
            }
        }
    }

    public function abrirdocumentosolicitacaoAction()
    {
        $idDocumento = $this->getRequest()->getParam('id', null);

        try {
            $vwSolicitacao = new Solicitacao_Model_vwPainelDeSolicitacaoProponente();
            $solicitacao = $vwSolicitacao->buscar(['idDocumento = ?' => $idDocumento])->current();

            if (empty($solicitacao))
                throw new Exception('Documento n&atilde;o encontrado!');

            $idProjeto = $solicitacao['idProjeto'] ? $solicitacao['idProjeto'] : false;
            $idPronac = $solicitacao['idPronac'] ? $solicitacao['idPronac'] : false;

            # verificar se o usuario tem permissao para acessar este documento por meio do id do projeto/proposta
            $permissao = parent::verificarPermissaoAcesso($idProjeto, $idPronac, false, true);

            if ($permissao['status'] === false)
                throw new Exception('Voc&ecirc; n&atilde;o tem permiss&atilde;o para baixar esse arquivo!');

            parent::abrirDocumento($idDocumento);

        } catch (Exception $e) {
            throw $e;
        }
    }


    public function contarSolicitacoesNaoLidasAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $resultado = 0;

        $vwSolicitacoes = new Solicitacao_Model_vwPainelDeSolicitacaoProponente();

        if ($this->usuario['usu_codigo']) {
            $resultado = $vwSolicitacoes->contarSolicitacoesNaoRespondidasTecnico($this->idUsuario, $this->grupoAtivo->codOrgao);
        } else {
            $resultado = $vwSolicitacoes->contarSolicitacoesNaoLidasUsuario($this->idUsuario, $this->idAgente);
        }

        $this->_helper->json(array('status' => true, 'msg' => $resultado));
    }
}