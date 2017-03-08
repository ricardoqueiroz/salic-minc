<?php

class Admissibilidade_AdmissibilidadeController extends MinC_Controller_Action_Abstract
{

    private $idPreProjeto = null;
    private $idUsuario = null;
    private $intTamPag = 50;
    private $codOrgaoSuperior = null;
    private $codGrupo = null;
    private $codOrgao = null;
    private $COD_CLASSIFICACAO_DOCUMENTO = 23;

    public function init()
    {
        $auth = Zend_Auth::getInstance(); // instancia da autenticacao

        // verifica as permissoes
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 90;  // Protocolo - Documento
        $PermissoesGrupo[] = 91;  // Protocolo - Recebimento
        $PermissoesGrupo[] = 92;  // Tecnico de Admissibilidade
        $PermissoesGrupo[] = 93;  // Coordenador de Parecerista
        $PermissoesGrupo[] = 94;  // Parecerista
        $PermissoesGrupo[] = 95;  // Consulta
        $PermissoesGrupo[] = 96;  // Consulta Gerencial
        $PermissoesGrupo[] = 97;  // Gestor do SALIC
        $PermissoesGrupo[] = 99;  // Acompanhamento
        $PermissoesGrupo[] = 100; // Prestacao de Contas
        $PermissoesGrupo[] = 103; // Coordenador de Analise
        $PermissoesGrupo[] = 104; // Protocolo - Envio / Recebimento
        $PermissoesGrupo[] = 110; // Tecnico de Analise
        $PermissoesGrupo[] = 113; // Coordenador de Arquivo
        $PermissoesGrupo[] = 114; // Coordenador de Editais
        $PermissoesGrupo[] = 115; // Atendimento Representacoes
        $PermissoesGrupo[] = 119; // Presidente da CNIC
        $PermissoesGrupo[] = 120; // Coordenador CNIC
        $PermissoesGrupo[] = 121; // Tecnico de Acompanhamento
        $PermissoesGrupo[] = 122; // Coordenador de Acompanhamento
        $PermissoesGrupo[] = 123; // Coordenador Geral de Acompanhamento
        $PermissoesGrupo[] = 124; // Tecnico de Prestacao de Contas
        $PermissoesGrupo[] = 125; // Coordenador de Prestacao de Contas
        $PermissoesGrupo[] = 127; // Coordenador de Atendimento
        $PermissoesGrupo[] = 128; // Tecnico de Portaria
        $PermissoesGrupo[] = 131; // Coordenador de Admissibilidade
        $PermissoesGrupo[] = 133; // Membros Natos da CNIC
        $PermissoesGrupo[] = 134; // Coordenador de Fiscalizacao
        $PermissoesGrupo[] = 135; // Tecnico de Fiscalizacao
        $PermissoesGrupo[] = 136; // Coordenador de Entidade Vinculada
        $PermissoesGrupo[] = 138; // Coordenador de Avaliacao
        $PermissoesGrupo[] = 139; // Tecnico de Avaliacao
        $PermissoesGrupo[] = 140; // Tecnico de Admissibilidade Edital
        //parent::perfil(1, $PermissoesGrupo);
        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);
        parent::init();

        if (!empty ($_REQUEST['idPreProjeto'])) {
            $this->idPreProjeto = $_REQUEST['idPreProjeto'];
        }

        isset($auth->getIdentity()->usu_codigo) ? $this->idUsuario = $auth->getIdentity()->usu_codigo : $this->idUsuario = $auth->getIdentity()->IdUsuario;
        //$this->idUsuario = $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        if (isset($auth->getIdentity()->usu_codigo)) {

            $this->codGrupo = $GrupoAtivo->codGrupo;
            $this->codOrgao = $GrupoAtivo->codOrgao;
            $this->codOrgaoSuperior = (!empty($auth->getIdentity()->usu_org_max_superior)) ? $auth->getIdentity()->usu_org_max_superior : null;
        }
    }

    public function indexAction()
    {
        $this->_redirect("/admissibilidade/admissibilidade/listar-propostas");
    }

    public function validarAcessoAdmissibilidade()
    {
        if (empty($this->idPreProjeto)) {
            parent::message("Necessário informar o n&uacute;mero da proposta.", "/admissibilidade/admissibilidade/listar-propostas", "ALERT");
        }
    }

    public function listarpropostasproponenteAction()
    {

    }

    public function exibirpropostaculturalAction()
    {
        $idPreProjeto = $this->idPreProjeto;
        $dados = Proposta_Model_AnalisarPropostaDAO::buscarGeral($idPreProjeto);
        $this->view->itensGeral = $dados;

        $movimentacao = new Proposta_Model_DbTable_TbMovimentacao();
        $movimentacao = $movimentacao->buscarStatusAtualProposta($idPreProjeto);
        $this->view->movimentacao = $movimentacao['Movimentacao'];

        //========== inicio codigo dirigente ================
        $arrMandatos = array();
        $this->view->mandatos = $arrMandatos;
        $preProjeto = new Proposta_Model_DbTable_PreProjeto();
        $rsDirigentes = array();

        $Empresa = $preProjeto->buscar(array('idPreProjeto = ?' => $this->idPreProjeto))->current();
        $idEmpresa = $Empresa->idAgente;

        $Projetos = new Projetos();
        $dadosProjeto = $Projetos->buscar(array('idProjeto = ?' => $this->idPreProjeto))->current();

        // Busca na tabela apoio ExecucaoImediata stproposta
        $tableVerificacao = new Proposta_Model_DbTable_Verificacao();
        if( !empty($this->view->itensGeral[0]->stProposta))
            $this->view->ExecucaoImediata = $tableVerificacao->findBy(array('idVerificacao' => $this->view->itensGeral[0]->stProposta));

        $Pronac = null;
        if (count($dadosProjeto) > 0) {
            $Pronac = $dadosProjeto->AnoProjeto . $dadosProjeto->Sequencial;
        }
        $this->view->Pronac = $Pronac;

        if (isset($dados[0]->CNPJCPFdigirente) && $dados[0]->CNPJCPFdigirente != "") {
            $tblAgente = new Agente_Model_DbTable_Agentes();
            $tblNomes = new Nomes();
            foreach ($dados as $v) {
                $rsAgente = $tblAgente->buscarAgenteENome(array('CNPJCPF=?' => $v->CNPJCPFdigirente))->current();
                $rsDirigentes[$rsAgente->idAgente]['CNPJCPFDirigente'] = $rsAgente->CNPJCPF;
                $rsDirigentes[$rsAgente->idAgente]['idAgente'] = $rsAgente->idAgente;
                $rsDirigentes[$rsAgente->idAgente]['NomeDirigente'] = $rsAgente->Descricao;
            }

            $tbDirigenteMandato = new tbAgentesxVerificacao();
            foreach ($rsDirigentes as $dirigente) {
                $rsMandato = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idEmpresa, 'idDirigente = ?' => $dirigente['idAgente'], 'stMandato = ?' => 0));
                $NomeDirigente = $dirigente['NomeDirigente'];
                $arrMandatos[$NomeDirigente] = $rsMandato;
            }
        }

        $this->view->dirigentes = $rsDirigentes;
        $this->view->mandatos = $arrMandatos;
        //============== fim codigo dirigente ================

        $propostaPorEdital = false;
        if ($this->view->itensGeral[0]->idEdital && $this->view->itensGeral[0]->idEdital != 0) {
            $propostaPorEdital = true;
        }
        $this->view->isEdital = $propostaPorEdital;
        $this->view->itensTelefone = Proposta_Model_AnalisarPropostaDAO::buscarTelefone($this->view->itensGeral[0]->idAgente);
        $this->view->itensPlanosDistribuicao = Proposta_Model_AnalisarPropostaDAO::buscarPlanoDeDistribucaoProduto($idPreProjeto);
        $this->view->itensFonteRecurso = Proposta_Model_AnalisarPropostaDAO::buscarFonteDeRecurso($idPreProjeto);
        $this->view->itensLocalRealiazacao = Proposta_Model_AnalisarPropostaDAO::buscarLocalDeRealizacao($idPreProjeto);
        $this->view->itensDeslocamento = Proposta_Model_AnalisarPropostaDAO::buscarDeslocamento($idPreProjeto);
        $this->view->itensPlanoDivulgacao = Proposta_Model_AnalisarPropostaDAO::buscarPlanoDeDivulgacao($idPreProjeto);

        //DOCUMENTOS ANEXADOS PROPOSTA
        $tbl = new Proposta_Model_DbTable_TbDocumentosPreProjeto();
        $rs = $tbl->buscarDocumentos(array("idProjeto = ?" => $this->idPreProjeto));
        $this->view->arquivosProposta = $rs;

        //DOCUMENTOS ANEXADOS PROPONENTE
        $tbA = new Proposta_Model_DbTable_TbDocumentosAgentes();
        $rsA = $tbA->buscarDocumentos(array("idAgente = ?" => $dados[0]->idAgente));
        $this->view->arquivosProponente = $rsA;

        //DOCUMENTOS ANEXADOS NA DILIGENCIA
        $tblAvaliacaoProposta = new AvaliacaoProposta();
        $rsAvaliacaoProposta = $tblAvaliacaoProposta->buscar(array("idProjeto = ?" => $idPreProjeto, "idArquivo ?" => new Zend_Db_Expr("IS NOT NULL")));
        $tbArquivo = new tbArquivo();
        $arrDadosArquivo = array();
        $arrRelacionamentoAvaliacaoDocumentosExigidos = array();
        if (count($rsAvaliacaoProposta) > 0) {
            foreach ($rsAvaliacaoProposta as $avaliacao) {
                $arrDadosArquivo[$avaliacao->idArquivo] = $tbArquivo->buscar(array("idArquivo = ?" => $avaliacao->idArquivo));
                $arrRelacionamentoAvaliacaoDocumentosExigidos[$avaliacao->idArquivo] = $avaliacao->idCodigoDocumentosExigidos;
            }
        }
        $this->view->relacionamentoAvaliacaoDocumentosExigidos = $arrRelacionamentoAvaliacaoDocumentosExigidos;
        $this->view->itensDocumentoPreProjeto = $arrDadosArquivo;

        //PEGANDO RELACAO DE DOCUMENTOS EXIGIDOS(GERAL, OU SEJA, TODO MUNDO)
        $tblDocumentosExigidos = new DocumentosExigidos();
        $rsDocumentosExigidos = $tblDocumentosExigidos->buscar()->toArray();
        $arrDocumentosExigidos = array();
        foreach ($rsDocumentosExigidos as $documentoExigido) {
            $arrDocumentosExigidos[$documentoExigido["Codigo"]] = $documentoExigido;
        }
        $this->view->documentosExigidos = $arrDocumentosExigidos;
        $this->view->itensHistorico = Proposta_Model_AnalisarPropostaDAO::buscarHistorico($idPreProjeto);
        $this->view->itensPlanilhaOrcamentaria = Proposta_Model_AnalisarPropostaDAO::buscarPlanilhaOrcamentaria($idPreProjeto);

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $tbPlanilhaEtapa = new Proposta_Model_DbTable_TbPlanilhaEtapa();
        $buscarEtapa = $tbPlanilhaEtapa->listarEtapasProdutos($this->idPreProjeto);

        $this->view->Etapa = $buscarEtapa;

        $preProjeto = new Proposta_Model_DbTable_PreProjeto();

        $buscarItem = $preProjeto->listarItensProdutos($this->idPreProjeto);
        $this->view->AnaliseCustos = Proposta_Model_DbTable_PreProjeto::analiseDeCustos($this->idPreProjeto);

        $this->view->idPreProjeto = $this->idPreProjeto;
        $pesquisaView = $this->_getParam('pesquisa');
        if ($pesquisaView == 'proposta') {
            $this->view->menu = 'inativo';
            $this->view->tituloTopo = 'Consultar dados da proposta';
        }

        if ($propostaPorEdital) {
            $tbFormDocumentoDAO = new tbFormDocumento();
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?' => $this->view->itensGeral[0]->idEdital, 'idClassificaDocumento = ?' => $this->COD_CLASSIFICACAO_DOCUMENTO));

            //busca o nome do EDITAL
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?' => $this->view->itensGeral[0]->idEdital));
            $nmEdital = $edital[0]->nmFormDocumento;
            $this->view->nmEdital = $nmEdital;

            $arrPerguntas = array();
            $arrRespostas = array();
            $tbPerguntaDAO = new tbPergunta();
            $tbRespostaDAO = new tbResposta();

            foreach ($edital as $registro) {
                $questoes = $tbPerguntaDAO->montarQuestionario($registro["nrFormDocumento"], $registro["nrVersaoDocumento"]);
                $questionario = '';
                if (is_object($questoes) and count($questoes) > 0) {
                    foreach ($questoes as $questao) {
                        $resposta = '';
                        $where = array(
                            'nrFormDocumento = ?' => $registro["nrFormDocumento"]
                        , 'nrVersaoDocumento = ?' => $registro["nrVersaoDocumento"]
                        , 'nrPergunta = ?' => $questao->nrPergunta
                        , 'idProjeto = ?' => $this->idPreProjeto
                        );
                        $resposta = $tbRespostaDAO->buscar($where);
                        $arrPerguntas[$registro["nrFormDocumento"]]["titulo"] = $registro["nmFormDocumento"];
                        $arrPerguntas[$registro["nrFormDocumento"]]["pergunta"][] = $questao->toArray();
                        $arrRespostas[] = $resposta->toArray();
                    }
                }
            }
            $this->view->perguntas = $arrPerguntas;
            $this->view->respostas = $arrRespostas;

            $this->montaTela("admissibilidade/proposta-por-edital.phtml");
        } else {
            $this->montaTela("admissibilidade/proposta-por-incentivo-fiscal.phtml");
        }
    }

    public function abrirDocumentosAnexadosAction()
    {
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout

        $idProjeto = $this->_request->getParam("idProjeto");
        $id = $this->_request->getParam('id');
        $tipo = $this->_request->getParam('tipo');
        $tipoDoc = null;
        $bln = "false";

        if ($tipo == '1') {
            $tipoDoc = "tbDocumentosAgentes"; //SAC.dbo.tbDocumentosAgentes
        } else if ($tipo == '2') {
            $tipoDoc = "tbDocumentosPreProjeto"; //SAC.dbo.tbDocumentosPreProjeto
        } else if ($tipo == '3') {
            $tipoDoc = "tbDocumento"; //SAC.dbo.tbDocumento
        }

        @ini_set("mssql.textsize", 10485760);
        @ini_set("mssql.textlimit", 10485760);
        @ini_set("upload_max_filesize", "10M");

        $resultado = UploadDAO::abrirdocumentosanexados($id, $tipoDoc);
        if (count($resultado) > 0) {
            if ($tipo == 1) {
                $this->_forward("abrirdocumentosanexadosbinario", "upload", "", array('id' => $id, 'busca' => $tipoDoc));
            } else {
                $this->_forward("abrirdocumentosanexados", "upload", "", array('id' => $id, 'busca' => $tipoDoc));
            }
            $bln = "true";
        }

        if ($bln == "false") {
            $url = Zend_Controller_Front::getInstance()->getBaseUrl() . "/consultardadosprojeto/?idPronac={$idPronac}";
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->flashMessenger->addMessage("N&atilde;o foi poss&iacute;vel abrir o arquivo especificado. Tente anex&aacute;-lo novamente.");
            $this->_helper->flashMessengerType->addMessage("ERROR");
            JS::redirecionarURL($url);
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }

    public function abrirDocumentosAnexadosAdmissibilidadeAction()
    {
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout

        $idProjeto = $this->_request->getParam("idProjeto");
        $id = $this->_request->getParam('id');
        $tipo = $this->_request->getParam('tipo');
        $tipoDoc = null;
        $bln = "false";

        if ($tipo == '1') {
            $tipoDoc = "tbDocumentosAgentes"; //SAC.dbo.tbDocumentosAgentes
        } else if ($tipo == '2') {
            $tipoDoc = "tbDocumentosPreProjeto"; //SAC.dbo.tbDocumentosPreProjeto
        } else if ($tipo == '3') {
            $tipoDoc = "tbDocumento"; //SAC.dbo.tbDocumento
        }

        @ini_set("mssql.textsize", 10485760);
        @ini_set("mssql.textlimit", 10485760);
        @ini_set("upload_max_filesize", "10M");

        $resultado = UploadDAO::abrirdocumentosanexados($id, $tipoDoc);
        if (count($resultado) > 0) {
            $this->_forward("abrirdocumentosanexados", "upload", "", array('id' => $id, 'busca' => $tipoDoc));
            $bln = "true";
        }

        if ($bln == "false") {
            $url = Zend_Controller_Front::getInstance()->getBaseUrl() . "/consultardadosprojeto/?idPronac={$idPronac}";
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->flashMessenger->addMessage("N&atilde;o foi poss&iacute;vel abrir o arquivo especificado. Tente anex&aacute;-lo novamente.");
            $this->_helper->flashMessengerType->addMessage("ERROR");
            JS::redirecionarURL($url);
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }


    public function incluiravaliacaoAction()
    {
        $this->validarAcessoAdmissibilidade();
        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?" => $this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto = strip_tags($rsProposta->NomeProjeto);
        $this->view->dataAtual = date("d/m/Y");
        $this->view->dataAtualBd = date("Y/m/d H:i:s");
    }

    public function salvaravaliacaoAction()
    {
        $post = Zend_Registry::get('post');
        $dados = array();
        $dados['idProjeto'] = $post->idPreProjeto;
        $dados['idTecnico'] = $this->idUsuario;
        $dados['dtEnvio'] = $post->dataAtual;
        $dados['dtAvaliacao'] = $post->dataAtual;
        $dados['avaliacao'] = $_POST['despacho'];
        $dados['ConformidadeOK'] = $post->conformidade;
        $dados['stEstado'] = 0;
        $dados['stEnviado'] = 'N';

        $projetoExiste = Proposta_Model_AnalisarPropostaDAO::verificarAvaliacao($post->idPreProjeto);

        //Esse if so existe por que nao existe objeto de negocio.
        if (count($projetoExiste) > 0) {
            $dados['dtEnvio'] = null;
        }

        $avaliacaoProposta = new tbAvaliacaoProposta();
        $avaliacaoProposta->inserir($dados);

        if ($dados['ConformidadeOK'] == 1) {
            $objTbMovimentacao = new Proposta_Model_DbTable_TbMovimentacao();
            $objTbMovimentacao->alterarConformidadeProposta($post->idPreProjeto, $this->idUsuario, Agente_Model_DbTable_Verificacao::PROPOSTA_EM_ANALISE_FINAL);
        }

        parent::message("Conformidade visual finalizada com sucesso!", "/admissibilidade/admissibilidade/exibirpropostacultural?idPreProjeto=" . $post->idPreProjeto . "&gravado=sim", "CONFIRM");
    }

    private function eviarEmail($idProjeto, $Mensagem, $pronac = null)
    {
        $auth = Zend_Auth::getInstance();
        $tbTextoEmailDAO = new tbTextoEmail();
        $preProjetosDAO = new Proposta_Model_DbTable_PreProjeto();
        $dadosProjeto = $preProjetosDAO->dadosProjetoDiligencia($idProjeto);
        $tbHistoricoEmailDAO = new tbHistoricoEmail();

        foreach ($dadosProjeto as $d) :
            //para Produ�?o comentar linha abaixo e para teste descomente ela
            //$email  =   'jailton.landim@cultura.gov.br';
            //para Produ�?o descomentar linha abaixo e para teste comente ela
            $email = trim(strtolower($d->Email));
            $mens = '<b>Proposta: ' . $d->idProjeto . ' - ' . $d->NomeProjeto . '<br> Proponente: ' . $d->Destinatario . '<br> </b>' . $Mensagem;
            $assunto = 'Avaliacao da proposta';
            if ($pronac) {
                $mens = '<b>Proposta: ' . $d->idProjeto . ' - ' . $d->NomeProjeto . '<br> Pronac: ' . $pronac . '<br> </b>' . $Mensagem;
                $assunto = 'Proposta transformada em Projeto Cultural';
            }
            $perfil = "PerfilGrupoPRONAC";

            $enviaEmail = EmailDAO::enviarEmail($email, $assunto, $mens, $perfil);

            $dados = array(
                'idProjeto' => $idProjeto,
                'idTextoemail' => new Zend_Db_Expr('NULL'),
                'iDAvaliacaoProposta' => new Zend_Db_Expr('NULL'),
                'DtEmail' => new Zend_Db_Expr('getdate()'),
                'stEstado' => 1,
                'idUsuario' => $auth->getIdentity()->usu_codigo,
            );

            $tbHistoricoEmailDAO->inserir($dados);
        endforeach;
    }

    public function analisedocumentalAction()
    {

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto = ?" => $this->idPreProjeto))->current();
        $this->view->proposta = $rsProposta;

        $tblAgente = new Agente_Model_DbTable_Agentes();
        $rsAgente = $tblAgente->buscarAgenteENome(array("a.idAgente = ?" => $rsProposta->idAgente))->current();
        $this->view->agente = $rsAgente;

        $idPreProjeto = $this->idPreProjeto;
        $dao = new Proposta_Model_AnalisarPropostaDAO();
        $model = new Proposta_Model_DbTable_DocumentosExigidos();
        $this->view->itensDocumentoPendente = $model->buscarDocumentoPendente($idPreProjeto);

        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function buscardocumentoAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $get = Zend_Registry::get('get');
        $idOpcao = $get->idOpcao;
        $idDocumento = $get->idDocumento;
        $tbl = new Proposta_Model_DbTable_DocumentosExigidos();
        $options = $tbl->buscarDocumentoOpcao($idOpcao);

        $selected = "";
        $htmlOptions = "<option value=''> - Selecione - </option>";
        foreach ($options as $option) {
            $selected = "";
            if ($option['codigo'] == $idDocumento) {
                $selected = "selected='selected' ";
            }
            $htmlOptions .= "<option value='{$option['codigo']}' {$selected}>" . ucfirst(((($option['descricao'])))) . " </option>";
        }
        echo $htmlOptions;
    }

    public function inserirdocumentoAction()
    {

        $dao = new Proposta_Model_AnalisarPropostaDAO();
        $post = Zend_Registry::get('post');
        $dados = array();
        $dados['idPreProjeto'] = $this->idPreProjeto;
        $dados['CodigoDocumento'] = $post->documento;

        try {
            if ($post->tipoDocumento == 1) {
                Proposta_Model_AnalisarPropostaDAO::inserirDocumentoProponente($dados);
            } else {
                Proposta_Model_AnalisarPropostaDAO::inserirDocumentoProjeto($dados);
            }

            //inserir avaliacao
            $tblAvaliacao = new AvaliacaoProposta();
            $dadosAvaliacao["idProjeto"] = $this->idPreProjeto;
            $dadosAvaliacao["idTecnico"] = $this->idUsuario;
            $dadosAvaliacao["DtEnvio"] = date("Y-m-d H:i:s");
            $dadosAvaliacao["DtAvaliacao"] = date("Y-m-d H:i:s");
            $dadosAvaliacao["Avaliacao"] = "Documenta&ccedil;&atilde;o pendente";
            $dadosAvaliacao["ConformidadeOK"] = 1;
            $dadosAvaliacao["stEstado"] = 1;
            $dadosAvaliacao["idCodigoDocumentosExigidos"] = $post->documento;

            $tblAvaliacao->inserir($dadosAvaliacao);
            $where = array(
                'CONVERT(VARCHAR,DtEnvio,103) = ?' => new Zend_Db_Expr('CONVERT(VARCHAR,GETDATE(),103)'),
                'idProjeto = ?' => $this->idPreProjeto,
                'idCodigoDocumentosExigidos is not null' => ''
            );
            $docs = $tblAvaliacao->buscar($where);

            if (count($docs) == 1) { //So poder enviar um email

                $msg = new Zend_Config_Ini(getcwd() . '/public/admissibilidade/mensagens_email_proponente.ini', 'pendencia_documental');

                $this->eviarEmail($this->idPreProjeto, $msg->msg);

            }


            parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/admissibilidade/analisedocumental?idPreProjeto=" . $this->idPreProjeto, "CONFIRM");

//            // Retornando proposta para movimentacao 95
//            $dadosMovimentacao['idProjeto'] = $this->idPreProjeto;
//            $dadosMovimentacao['Movimentacao'] = 95;
//            $dadosMovimentacao['DtMovimentacao'] = date("Y-m-d");
//            $dadosMovimentacao['stEstado'] = 0;
//            $dadosMovimentacao['Usuario'] = $this->idUsuario;
//
//            $tblMovimentacao = new Proposta_Model_DbTable_TbMovimentacao();
//            //Mudando as movimentacoes anteriores para o stEstado = 1
//
//            $rsRetorno = $tblMovimentacao->update(array("stEstado"=>1), "idProjeto = {$this->idPreProjeto}");
//
//            $rsMovimentacao = $tblMovimentacao->inserir($dadosMovimentacao);

            // Verificando se movimentacao ja existe
//            $rsBuscaMovimentacao = $tblMovimentacao->buscar(array("Movimentacao = ?"=>97, "idProjeto = ?"=>$dadosMovimentacao['idProjeto']));
//            if($rsBuscaMovimentacao->count() < 1){
//                // Salvando movimentacao
//                $rsMovimentacao = $tblMovimentacao->salvar($dadosMovimentacao);
//            }
        } catch (Exception $e) {
            parent::message("Erro ao realizar opera&ccedil;&atilde;o", "/admissibilidade/admissibilidade/analisedocumental?idPreProjeto=" . $this->idPreProjeto, "ERROR");
        }


    }

    public function updatedocumentoAction()
    {
        $dao = new Proposta_Model_AnalisarPropostaDAO();
        $post = Zend_Registry::get('post');
        $dado = array();
        $dado['idPreProjeto'] = $post->idprojeto;
        $dado['CodigoDocumento'] = $post->documento;
        $dado['iddocantigo'] = $post->iddocantigo;

        if ($post->tipoDocumento == 1) {
            Proposta_Model_AnalisarPropostaDAO::updateDocumentoProponente($dado);
        } else {
            Proposta_Model_AnalisarPropostaDAO::updateDocumentoProjeto($dado);
        }

        //Enviar e-mail
        //historico do e-mail
        parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/admissibilidade/analisedocumental?idPreProjeto=" . $this->idPreProjeto, "CONFIRM");

    }

    public function deletedocumentoAction()
    {
        $dao = new Proposta_Model_AnalisarPropostaDAO();
        $get = Zend_Registry::get('get');


        if ($get->tipoDocumento == 1) {
            Proposta_Model_AnalisarPropostaDAO::deleteDocumentoProponente($get->idDocumento);
        } else {
            Proposta_Model_AnalisarPropostaDAO::deleteDocumentoProjeto($get->idDocumento);
        }

        //Enviar e-mail
        //historico do e-mail
        parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/admissibilidade/analisedocumental?idPreProjeto=" . $this->idPreProjeto, "CONFIRM");

    }

    public function despacharpropostaAction()
    {
        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();

        $dao = new Proposta_Model_AnalisarPropostaDAO();
        $this->view->itensDespacho = Proposta_Model_AnalisarPropostaDAO::buscarDespacho($this->idPreProjeto);
    }

    public function transformarPropostaEmProjetoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $get = Zend_Registry::get('get');

        $this->validarAcessoAdmissibilidade();
        $auth = Zend_Auth::getInstance();
        $idOrgao = $auth->getIdentity()->usu_orgao;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto = ?" => $this->idPreProjeto))->current();

        //Buscando produto principal
        $tblPlanoDistribuicao = new PlanoDistribuicao();
        $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscar(array("idProjeto = ?" => $this->idPreProjeto, "stPrincipal = ?" => 1))->current();

        $tblOrgaos = new Orgaos();
        if ($rsProposta->idEdital == 0 || empty($rsProposta->idEdital)) {
            //Se existe plano de distribuicao, entao pega-se o orgao baseado no produto principal
            $rsOrgaos = $tblOrgaos->buscarOrgaoPorSegmento($rsPlanoDistribuicao->Segmento)->current();
            //$idOrgao = $rsOrgaos->Codigo;
            $idOrgao = $this->codOrgao;
        } else {
            //Se nao existe plano de distribuicao, entao esta e uma proposta por edital,
            //entao pega-se o orgao do edital
            $tblEdital = new Edital();
            $rsEdital = $tblEdital->buscar(array("idEdital = ?" => $rsProposta->idEdital))->current();

            $rsOrgaos = $tblOrgaos->buscar(array("Codigo = ?" => $rsEdital->idOrgao))->current();
            //$idOrgao = $rsOrgaos->Codigo;
            $idOrgao = $this->codOrgao;
        }

        $tblAgente = new Agente_Model_DbTable_Agentes();
        $rsAgente = $tblAgente->buscarAgenteENome(array("a.idAgente = ?" => $rsProposta->idAgente))->current();

        $cnpjcpf = $rsAgente->CNPJCPF;

        $wsWebServiceSEI = new ServicosSEI();

        $arrRetornoGerarProcedimento = $wsWebServiceSEI->wsGerarProcedimento();
        $chars = array(".", "/", "-");
        $nrProcessoSemFormatacao = str_replace($chars, "", $arrRetornoGerarProcedimento->ProcedimentoFormatado);
        $nrProcesso = $nrProcessoSemFormatacao;

        $retorno = array();
        try {
            $this->incluirProjeto($this->idPreProjeto, $cnpjcpf, $idOrgao, $this->idUsuario, $nrProcesso, $rsProposta->stProposta);
            $tblProjeto = new Projetos();
            $rsProjeto = $tblProjeto->buscar(array("idProjeto = ?" => $this->idPreProjeto), "IdPRONAC DESC")->current();
            if (!empty($rsProjeto)) {
                $nrPronac = $rsProjeto->AnoProjeto . $rsProjeto->Sequencial;
                $retorno['sucesso'] = "A Proposta " . $this->idPreProjeto . " foi transformada no Projeto No. " . $nrPronac;
            }
        } catch (Exception $objException) {
            $retorno['erro'] = 'Erro ao tentar transformar proposta em projeto!';
        }
        header('Content-Type: application/json');
        echo json_encode($retorno);
    }

    public function encaminharpropostaAction()
    {
        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();
        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?" => $this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto = isset($rsProposta->NomeProjeto) ? strip_tags($rsProposta->NomeProjeto) : '';
    }

    public function salvardespachoAction()
    {
        $post = Zend_Registry::get('post');

        $dados = array();
        $dados['idPreProjeto'] = $post->idPreProjeto;
        $dados['idTecnico'] = $this->idUsuario;
        $dados['despacho'] = trim($post->despacho);

//        $despachoExiste = Proposta_Model_AnalisarPropostaDAO::verificarDespacho($post->idPreProjeto);

//        if(count($despachoExiste)>0){
//            Proposta_Model_AnalisarPropostaDAO::updateEstadoDespacho($post->idPreProjeto);
//        }

        Proposta_Model_AnalisarPropostaDAO::inserirDespacho($dados);

        //if($despachoExiste[0]->Tipo == 129 ){
//        $movimentacaoExiste = Proposta_Model_AnalisarPropostaDAO::verificarMovimentcaoDespacho($post->idPreProjeto);

//        if(count($movimentacaoExiste)>0 && $movimentacaoExiste[0]->Movimentacao == 127)
//        {
//            $dados['movimentacao'] = Agente_Model_DbTable_Verificacao::PROPOSTA_EM_ANALISE_FINAL;
//
//            //APAGA DOCUMENTOS PENDENTES CONFORME REGRA DA TRIGGER
//            Proposta_Model_AnalisarPropostaDAO::deleteDocumentoProponentePeloProjeto($post->idPreProjeto);
//            Proposta_Model_AnalisarPropostaDAO::deleteDocumentoProjetoPeloProjeto($post->idPreProjeto);
//
//        }else{
//            $dados['movimentacao'] = 127;
//        }

//        Proposta_Model_AnalisarPropostaDAO::updateEstadoMovimentacao($post->idPreProjeto);
//        Proposta_Model_AnalisarPropostaDAO::inserirMovimentacao($dados);
        //}

        //Envia Email
//        $msg = new Zend_Config_Ini(getcwd().'/public/admissibilidade/mensagens_email_proponente.ini', 'sem_pendencia_documental');
//        $this->eviarEmail($this->idPreProjeto,$msg->msg);

        if (isset($post->devolver) && $post->devolver == 1) {
            parent::message("Mensagem enviada com sucesso!", "/admissibilidade/admissibilidade/gerenciamentodepropostas", "CONFIRM");
            return true;
            $this->_helper->viewRenderer->setNoRender(TRUE);
        } else {
            parent::message("Despacho encaminhado com sucesso!", "/admissibilidade/admissibilidade/listar-propostas", "CONFIRM");
            return;
        }
    }

    public function arquivarpropostaAction()
    {
        $dao = new Proposta_Model_AnalisarPropostaDAO();
        try {
            //Enviar e-mail informando arquivamento e a justificativa
            $tblPreProjeto = new Proposta_Model_DbTable_PreProjeto();
            $rsPreProjeto = $tblPreProjeto->find($this->idPreProjeto)->current();
            $rsPreProjeto->DtArquivamento = date("Y/m/d H:i:s");
            $rsPreProjeto->stEstado = 0;
            $rsPreProjeto->save();

            parent::message("Opera&ccedil;&atilde;o realizada com sucesso!", "/admissibilidade/admissibilidade/listar-propostas", "CONFIRM");
        } catch (Exception $e) {
            parent::message("Erro ao realizar opera&ccedil;&atilde;o!", "/admissibilidade/admissibilidade/listar-propostas", "ERROR");
        }
    }

    public function confirmararquivarpropostaAction()
    {
        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?" => $this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto = strip_tags($rsProposta->NomeProjeto);
    }

    public function arquivarAction()
    {
        $dao = new Proposta_Model_AnalisarPropostaDAO();
        $post = Zend_Registry::get('post');
        Proposta_Model_AnalisarPropostaDAO::deletePreProjeto($post->idprojeto);
        ///Enviar e-mail informando arquivamento e a justificativa


    }

    public function imprimirpropostaculturalAction()
    {

        $this->_helper->layout->disableLayout();

        $idPreProjeto = $this->idPreProjeto;
        $dados = Proposta_Model_AnalisarPropostaDAO::buscarGeral($idPreProjeto);
        $this->view->itensGeral = $dados;

        //========== inicio codigo dirigente ================
        $arrMandatos = array();
        $this->view->mandatos = $arrMandatos;
        $preProjeto = new Proposta_Model_DbTable_PreProjeto();
        $rsDirigentes = array();

        $Empresa = $preProjeto->buscar(array('idPreProjeto = ?' => $this->idPreProjeto))->current();
        $idEmpresa = $Empresa->idAgente;
//xd($this->view->itensGeral);

        $Projetos = new Projetos();
        $dadosProjeto = $Projetos->buscar(array('idProjeto = ?' => $this->idPreProjeto))->current();

        // Busca na tabela apoio ExecucaoImediata stproposta
        //  xd($this->idPreProjeto);
        $tableVerificacao = new Proposta_Model_DbTable_Verificacao();
        if( !empty($this->view->itensGeral[0]->stProposta))
            $this->view->ExecucaoImediata = $tableVerificacao->findBy(array('idVerificacao' => $this->view->itensGeral[0]->stProposta));

        $Pronac = null;
        if (count($dadosProjeto) > 0) {
            $Pronac = $dadosProjeto->AnoProjeto . $dadosProjeto->Sequencial;
        }
        $this->view->Pronac = $Pronac;

        if (isset($dados[0]->CNPJCPFdigirente) && $dados[0]->CNPJCPFdigirente != "") {
            $tblAgente = new Agente_Model_DbTable_Agentes();
            $tblNomes = new Nomes();
            foreach ($dados as $v) {
                $rsAgente = $tblAgente->buscarAgenteENome(array('CNPJCPF=?' => $v->CNPJCPFdigirente))->current();
                $rsDirigentes[$rsAgente->idAgente]['CNPJCPFDirigente'] = $rsAgente->CNPJCPF;
                $rsDirigentes[$rsAgente->idAgente]['idAgente'] = $rsAgente->idAgente;
                $rsDirigentes[$rsAgente->idAgente]['NomeDirigente'] = $rsAgente->Descricao;
            }

            $tbDirigenteMandato = new tbAgentesxVerificacao();
            foreach ($rsDirigentes as $dirigente) {
                $rsMandato = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idEmpresa, 'idDirigente = ?' => $dirigente['idAgente'], 'stMandato = ?' => 0));
                $NomeDirigente = $dirigente['NomeDirigente'];
                $arrMandatos[$NomeDirigente] = $rsMandato;
            }
        }

        $this->view->dirigentes = $rsDirigentes;
        $this->view->mandatos = $arrMandatos;
        //============== fim codigo dirigente ================

        $propostaPorEdital = false;
        if ($this->view->itensGeral[0]->idEdital && $this->view->itensGeral[0]->idEdital != 0) {
            $propostaPorEdital = true;
        }
        $this->view->isEdital = $propostaPorEdital;
        $this->view->itensTelefone = Proposta_Model_AnalisarPropostaDAO::buscarTelefone($this->view->itensGeral[0]->idAgente);
        $this->view->itensPlanosDistribuicao = Proposta_Model_AnalisarPropostaDAO::buscarPlanoDeDistribucaoProduto($idPreProjeto);
        $this->view->itensFonteRecurso = Proposta_Model_AnalisarPropostaDAO::buscarFonteDeRecurso($idPreProjeto);
        $this->view->itensLocalRealiazacao = Proposta_Model_AnalisarPropostaDAO::buscarLocalDeRealizacao($idPreProjeto);
        $this->view->itensDeslocamento = Proposta_Model_AnalisarPropostaDAO::buscarDeslocamento($idPreProjeto);
        $this->view->itensPlanoDivulgacao = Proposta_Model_AnalisarPropostaDAO::buscarPlanoDeDivulgacao($idPreProjeto);

        //DOCUMENTOS ANEXADOS PROPOSTA
        $tbl = new Proposta_Model_DbTable_TbDocumentosPreProjeto();
        $rs = $tbl->buscarDocumentos(array("idProjeto = ?" => $this->idPreProjeto));
        $this->view->arquivosProposta = $rs;

        //DOCUMENTOS ANEXADOS PROPONENTE
        $tbA = new Proposta_Model_DbTable_TbDocumentosAgentes();
        $rsA = $tbA->buscarDocumentos(array("idAgente = ?" => $dados[0]->idAgente));
        $this->view->arquivosProponente = $rsA;

        //DOCUMENTOS ANEXADOS NA DILIGENCIA
        $tblAvaliacaoProposta = new AvaliacaoProposta();
        $rsAvaliacaoProposta = $tblAvaliacaoProposta->buscar(array("idProjeto = ?" => $idPreProjeto, "idArquivo ?" => new Zend_Db_Expr("IS NOT NULL")));
        $tbArquivo = new tbArquivo();
        $arrDadosArquivo = array();
        $arrRelacionamentoAvaliacaoDocumentosExigidos = array();
        if (count($rsAvaliacaoProposta) > 0) {
            foreach ($rsAvaliacaoProposta as $avaliacao) {
                $arrDadosArquivo[$avaliacao->idArquivo] = $tbArquivo->buscar(array("idArquivo = ?" => $avaliacao->idArquivo));
                $arrRelacionamentoAvaliacaoDocumentosExigidos[$avaliacao->idArquivo] = $avaliacao->idCodigoDocumentosExigidos;
            }
        }
        $this->view->relacionamentoAvaliacaoDocumentosExigidos = $arrRelacionamentoAvaliacaoDocumentosExigidos;
        $this->view->itensDocumentoPreProjeto = $arrDadosArquivo;

        //PEGANDO RELACAO DE DOCUMENTOS EXIGIDOS(GERAL, OU SEJA, TODO MUNDO)
        $tblDocumentosExigidos = new DocumentosExigidos();
        $rsDocumentosExigidos = $tblDocumentosExigidos->buscar()->toArray();
        $arrDocumentosExigidos = array();
        foreach ($rsDocumentosExigidos as $documentoExigido) {
            $arrDocumentosExigidos[$documentoExigido["Codigo"]] = $documentoExigido;
        }
        $this->view->documentosExigidos = $arrDocumentosExigidos;
        $this->view->itensHistorico = Proposta_Model_AnalisarPropostaDAO::buscarHistorico($idPreProjeto);
        $this->view->itensPlanilhaOrcamentaria = Proposta_Model_AnalisarPropostaDAO::buscarPlanilhaOrcamentaria($idPreProjeto);

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $tbPlanilhaEtapa = new Proposta_Model_DbTable_TbPlanilhaEtapa();
        $buscarEtapa = $tbPlanilhaEtapa->listarEtapasProdutos($this->idPreProjeto);

        $this->view->Etapa = $buscarEtapa;

        $preProjeto = new Proposta_Model_DbTable_PreProjeto();

        $buscarItem = $preProjeto->listarItensProdutos($this->idPreProjeto);
        $this->view->AnaliseCustos = Proposta_Model_DbTable_PreProjeto::analiseDeCustos($this->idPreProjeto);

        $this->view->idPreProjeto = $this->idPreProjeto;


        if ($propostaPorEdital) {
            $tbFormDocumentoDAO = new tbFormDocumento();
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?' => $this->view->itensGeral[0]->idEdital, 'idClassificaDocumento = ?' => $this->COD_CLASSIFICACAO_DOCUMENTO));
            $arrPerguntas = array();
            $arrRespostas = array();
            $tbPerguntaDAO = new tbPergunta();
            $tbRespostaDAO = new tbResposta();
            foreach ($edital as $registro) {

                $questoes = $tbPerguntaDAO->montarQuestionario($registro["nrFormDocumento"], $registro["nrVersaoDocumento"]);
                $questionario = '';
                if (is_object($questoes) and count($questoes) > 0) {
                    foreach ($questoes as $questao) {
                        $resposta = '';
                        $where = array(
                            'nrFormDocumento = ?' => $registro["nrFormDocumento"]
                        , 'nrVersaoDocumento = ?' => $registro["nrVersaoDocumento"]
                        , 'nrPergunta = ?' => $questao->nrPergunta
                        , 'idProjeto = ?' => $this->idPreProjeto
                        );
                        $resposta = $tbRespostaDAO->buscar($where);

                        $arrPerguntas[$registro["nrFormDocumento"]]["titulo"] = $registro["nmFormDocumento"];
                        $arrPerguntas[$registro["nrFormDocumento"]]["pergunta"][] = $questao->toArray();
                        $arrRespostas[] = $resposta->toArray();

                    }
                }
            }

            $this->view->perguntas = $arrPerguntas;
            $this->view->respostas = $arrRespostas;

            $this->montaTela("admissibilidade/imprimir-proposta-por-edital.phtml");
        } else {
            $this->montaTela("admissibilidade/imprimir-proposta-por-incentivo-fiscal.phtml");
        }


    }

    public function gerarpdfAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        //$post = Zend_Registry::get('post');

        $pdf = new PDFCreator($_POST['html']);
        if (isset($_GET['quebra_linha'])) {
            $pdf->gerarPdf($_GET['quebra_linha']);
        } else {
            $pdf->gerarPdf();
        }

    }

    public function listaranalisevisualtecnicoAction()
    {

    }

    public function consultarhistoricoanalisevisualAction()
    {

    }

    public function imprimiretiquetaprojetoAction()
    {

    }

    public function imprimiretiquetaprojetoconsultaAction()
    {

    }

    public function alterarunianalisepropostaconsultaAction()
    {

    }

    public function frmalterarunianalisepropostaAction()
    {
        $this->_helper->layout->disableLayout();
        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->unidadeAnaliseProposta($_POST["nrProposta"])->current();

        if ($rsProposta) {
            $rsOrgaoSecretaria = $tblProposta->orgaoSecretaria($rsProposta->idTecnico);
        } else {
            echo "<font color='black' size='2'><b>Nenhum registro encontrado</b></font>";
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }


        $this->view->orgaoUsuarioLogado = $this->codOrgaoSuperior;
        $this->view->proposta = $rsProposta;
        $this->view->orgaoSecretaria = $rsOrgaoSecretaria;
    }

    public function alterarunianalisepropostaAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        try {
            $tblTbAvaliacaoProposta = new AvaliacaoProposta();
            $rsAvaliacaoProposta = $tblTbAvaliacaoProposta->find($_POST["idAvaliacaoProposta"])->current();

            $params = new stdClass();
            $params->usu_cod = $rsAvaliacaoProposta->idTecnico;
            $params->idProjeto = $rsAvaliacaoProposta->idProjeto;
            AdmissibilidadeDAO::redistribuirAnalise($params);

            //parent::message("Localiza&ccedil;&atilde;o alterada com sucesso", "/admissibilidade/admissibilidade/alterarunianalisepropostaconsulta", "CONFIRM");
            echo "<font color='green' size='2'><b>Localiza&ccedil;&atilde;o alterada com sucesso</b></font>";
            $this->_helper->viewRenderer->setNoRender(TRUE);
        } catch (Exception $e) {
            //parent::message("Falha ao realizar opera&ccedil;&atilde;o", "/admissibilidade/admissibilidade/alterarunianalisepropostaconsulta", "CONFIRM");
            echo "<font color='red' size='2'><b>Falha ao realizar opera&ccedil;&atilde;o</b></font>";
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }

    public function redistribuiranaliseAction()
    {
            $orgao = new Orgaos();
            $vwPainelAvaliar = new Admissibilidade_Model_DbTable_VwPainelAvaliarPropostas();

            $orgao = $orgao->codigoOrgaoSuperior($this->codOrgao);
            $orgaoSuperior = $orgao[0]['Superior'];

            $where['idSecretaria = ?'] = $orgaoSuperior;

            $analistas = $vwPainelAvaliar->propostas($where,null, null,null);


            $this->view->analistas = array();
            $this->view->urlResumo = $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-distribuicao-propostas";
            $i = 0;
            foreach ($analistas as $analista) {
                $this->view->analistas[$analista->Tecnico][$i]['nrProposta'] = $analista->idProjeto;
                $this->view->analistas[$analista->Tecnico][$i]['NomeProjeto'] = $analista->NomeProposta;
                $this->view->analistas[$analista->Tecnico][$i]['DtMovimentacao'] = ConverteData($analista->DtAdmissibilidade, 5);;
                $this->view->analistas[$analista->Tecnico][$i]['fase'] = $analista->Fase;
                $i++;
            }
    }

    public function redistribuiranaliseitemAction()
    {
        if ($_REQUEST['idProjeto'] && isset($_REQUEST['usu_cod'])) {
            $params = new stdClass();
            $params->usu_cod = $_REQUEST['usu_cod'];
            $params->idProjeto = $_REQUEST['idProjeto'];
            AdmissibilidadeDAO::redistribuirAnalise($params);
            parent::message("An&aacute;lise redistribu&iacute;da com sucesso.", "/admissibilidade/admissibilidade/redistribuiranalise", "CONFIRM");
        }

        if ($_REQUEST['idProjeto'] ) {
            $this->view->analista = AdmissibilidadeDAO::consultarProposta($this->getRequest()->getParam('idProjeto'));

            $params = new stdClass();
            $params->idProjeto = $_REQUEST['idProjeto'];

            $orgao = new Orgaos();
            $orgao = $orgao->codigoOrgaoSuperior($this->codOrgao);
            $orgaoSuperior = $orgao[0]['Superior'];

            $params = new stdClass();
            $params->gru_codigo = $_SESSION['GrupoAtivo']['codOrgao'];
            $params->usu_orgao = $orgaoSuperior;
            $this->view->novosAnalistas = AdmissibilidadeDAO::consultarRedistribuirAnaliseItemSelect($params);
        }
    }

    public function gerenciaranalistasAction()
    {
        if ($this->codOrgao) {
            $params = new stdClass();
            $params->cod_grupo = $this->codGrupo;

            $orgao = new Orgaos();
            $orgao = $orgao->codigoOrgaoSuperior($this->codOrgao);

            $params->cod_orgao = $orgao[0]['Superior'];

            $this->view->analistas = AdmissibilidadeDAO::gerenciarAnalistas($params);
        }
    }

    public function gerenciaranalistaAction()
    {
        if ($_REQUEST['usu_cod'] && $_REQUEST['usu_orgao'] && $_REQUEST['gru_codigo'] && isset($_REQUEST['status'])) {
            $params = new stdClass();
            $params->uog_status = $_REQUEST['status'];
            $params->usu_cod = $_REQUEST['usu_cod'];
            $params->gru_codigo = $_REQUEST['gru_codigo'];
            $params->usu_orgao = $_REQUEST['usu_orgao'];

            $msgComplementar = "Altera&ccedil;&atilde;o realizada com sucesso!";

            if ((int)$params->uog_status === 0) {
                $tblPreProjeto = new Proposta_Model_DbTable_PreProjeto();
                $tecnicoTemProposta = $tblPreProjeto->tecnicoTemProposta($params->usu_cod);

                if ($tecnicoTemProposta) {
                    $msgComplementar = "O Analista foi desabilitado, por&eacute;m existem Propostas distribu&iacute;das para o mesmo!";
                }
            }

            $atualizar = AdmissibilidadeDAO::atualizarAnalista($params);
            parent::message($msgComplementar, "/admissibilidade/admissibilidade/gerenciaranalistas", "CONFIRM");
        }

        if ($_REQUEST['usu_cod'] && $_REQUEST['usu_orgao'] && $_REQUEST['gru_codigo']) {
            $params = new stdClass();
            $params->usu_cod = $_REQUEST['usu_cod'];
            $params->usu_orgao = $_REQUEST['usu_orgao'];
            $params->gru_codigo = $_REQUEST['gru_codigo'];
            $this->view->analista = AdmissibilidadeDAO::gerenciarAnalista($params);
        }
    }

    public function gerenciamentodepropostasAction()
    {

        //cod_grupo = 131 se perfil do tipo coordenador.
        //if($_SESSION['GrupoAtivo']['codOrgao'] != 160 && $_SESSION['GrupoAtivo']['codOrgao'] != 251) {
        if ($_SESSION['GrupoAtivo']['codGrupo'] != 131) {
            $this->view->mensagem = "Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar essa funcionalidade.";
        } else {
            $post = Zend_Registry::get('post');
            $numeroProposta = $post->numeroProposta;
            $tipoNome = $post->tiponome;
            $nomeProposta = $post->nomeProposta;
            $tipoCpf = $post->tipocpf;
            $cpfCnpj = retiraMascara($post->cpfCnpj);
            $analista = $post->analista;
            $tipodata = $post->tipodata;
            $dataPropostaInicial = $post->dataPropostaInicial;

            $arrBusca = array();
            //NUM. PROPOSTA
            if (!empty($numeroProposta)) {
                $arrBusca[" idPreProjeto "] = "'" . $numeroProposta . "'";
            }
            //NOME DA PROPOSTA
            if (!empty($nomeProposta)) {
                if ($tipoNome == "contendo") {
                    $arrBusca[" NomeProjeto LIKE "] = "'%" . $nomeProposta . "%'";
                } elseif ($tipoNome == "inicioIgual") {
                    $arrBusca[" NomeProjeto LIKE "] = "'" . $nomeProposta . "%'";
                }
            }
            //CPF / CNPJ PROPONENTE
            if (!empty($cpfCnpj)) {
                if ($tipoCpf == "contendo") {
                    $arrBusca[" CNPJCPF LIKE "] = "'%" . $cpfCnpj . "%'";
                } elseif ($tipoCpf == "igual") {
                    $arrBusca[" CNPJCPF "] = "'" . $cpfCnpj . "'";;
                } elseif ($tipoCpf == "inicioIgual") {
                    $arrBusca[" CNPJCPF LIKE "] = "'" . $cpfCnpj . "%'";;
                } elseif ($tipoCpf == "diferente") {
                    $arrBusca[" CNPJCPF <> "] = "'" . $cpfCnpj . "'";;
                }
            }
            //ANALISTA
            if (!empty($analista)) {
                $arrBusca[" idTecnico "] = "'" . $analista . "'";
            } elseif ($analista == "0") {
                $arrBusca[" idTecnico <> "] = "''";
            }

            if (!empty ($dataPropostaInicial) || $tipodata != '') {
                if ($tipodata == "igual") {
                    $arrBusca['x.DtAvaliacao > '] = "'" . ConverteData($post->dataPropostaInicial, 13) . " 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'" . ConverteData($post->dataPropostaInicial, 13) . " 23:59:59'";

                } elseif ($tipodata == "maior") {
                    $arrBusca['x.DtAvaliacao >= '] = "'" . ConverteData($post->dataPropostaInicial, 13) . " 00:00:00'";

                } elseif ($tipodata == "menor") {
                    $arrBusca['x.DtAvaliacao <= '] = "'" . ConverteData($post->dataPropostaInicial, 13) . " 00:00:00'";

                } elseif ($tipodata == "OT") {
                    $arrBusca['x.DtAvaliacao = '] = "'" . date("Y-m-") . (date("d") - 1) . " 00:00:00'";

                } elseif ($tipodata == "U7") {
                    $arrBusca['x.DtAvaliacao > '] = "'" . date("Y-m-") . (date("d") - 7) . " 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'" . date("Y-m-d") . " 23:59:59'";

                } elseif ($tipodata == "SP") {
                    $arrBusca['x.DtAvaliacao > '] = "'" . date("Y-m-") . (date("d") - 7) . " 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'" . date("Y-m-d") . " 23:59:59'";

                } elseif ($tipodata == "MM") {
                    $arrBusca['x.DtAvaliacao > '] = "'" . date("Y-m-01") . " 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'" . date("Y-m-d") . " 23:59:59'";

                } elseif ($tipodata == "UM") {
                    $arrBusca['x.DtAvaliacao > '] = "'" . date("Y-") . (date("m") - 1) . "-01 00:00:00'";
                    $arrBusca['x.DtAvaliacao < '] = "'" . date("Y-") . (date("m") - 1) . "-31 23:59:59'";

                } else {
                    $arrBusca['x.DtAvaliacao > '] = "'" . ConverteData($post->dataPropostaInicial, 13) . " 00:00:00'";

                    if ($post->dataPropostaFinal != "") {
                        $arrBusca['x.DtAvaliacao < '] = "'" . ConverteData($post->dataPropostaFinal, 13) . " 23:59:59'";
                    }
                }

            }
            //ORGAO USUARIO SUPERIOR LOGADO
            //$arrBusca[" SAC.dbo.fnIdOrgaoSuperiorAnalista(x.idTecnico) "] = $_SESSION['GrupoAtivo']['codOrgao'];
            $arrBusca[" SAC.dbo.fnIdOrgaoSuperiorAnalista(x.idTecnico) = "] = $this->codOrgaoSuperior;
            //$arrBusca[" TABELAS.dbo.fnCodigoOrgaoEstrutura(u.usu_orgao, 1) "] = $this->codOrgaoSuperior;


            $this->view->analistas = AdmissibilidadeDAO::consultarGerenciamentoProposta($arrBusca, array("Tecnico ASC"));

            if (!$this->view->analistas) {
                $this->view->mensagem = 'Nenhum registro encontrado';
            } else {
                $array = array();
                foreach ($this->view->analistas as $analistas) {

                    $array[$analistas->Tecnico][$analistas->idProjeto]['NomeProposta'] = $analistas->NomeProposta;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idAgente'] = $analistas->idAgente;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['CNPJCPF'] = $analistas->CNPJCPF;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idUsuario'] = $analistas->idUsuario;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['Tecnico'] = $analistas->Tecnico;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idSecretaria'] = $analistas->idSecretaria;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['DtAdmissibilidade'] = ConverteData($analistas->DtAdmissibilidade, 5);
                    $array[$analistas->Tecnico][$analistas->idProjeto]['dias'] = $analistas->dias;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idAvaliacaoProposta'] = $analistas->idAvaliacaoProposta;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['idMovimentacao'] = $analistas->idMovimentacao;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['stTipoDemanda'] = $analistas->stTipoDemanda;
                    $array[$analistas->Tecnico][$analistas->idProjeto]['stPlanoAnual'] = $analistas->stPlanoAnual;
                }
                $this->view->analistas = $array;

            }

            $this->view->urlResumo = $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-gerenciamento-proposta";
        }
    }

    public function devolverpropostaAction()
    {

        //verifica se id preprojeto foi enviado
        $this->validarAcessoAdmissibilidade();
        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscar(array("idPreProjeto=?" => $this->idPreProjeto))->current();
        $this->view->idPreProjeto = $this->idPreProjeto;
        $this->view->nomeProjeto = strip_tags($rsProposta->NomeProjeto);

    }

    public function propostaPorProponenteAction()
    {
        $get = Zend_Registry::get("get");
        $idAgente = $get->agente;
        $this->view->idPreProjeto = !empty($get->idPreProjeto) ? $get->idPreProjeto:null;
        $this->view->realizar_analise = !empty($get->realizar_analise) ? $get->realizar_analise:null;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsPropostas = $tblProposta->buscar(array("idagente = ?" => $idAgente), array("nomeprojeto ASC"));

        //Descobrindo os dados do Agente/Proponente
        $tblAgente = new Nomes();
        $rsAgente = $tblAgente->buscar(array("idAgente = ? " => $idAgente))->current();

        //Descobrindo a movimenta��o corrente de cada proposta
        if (count($rsPropostas) > 0) {
            //Conectando com movimentacao
            $tblMovimentacao = new Proposta_Model_DbTable_TbMovimentacao();
            //Conectando com projetos
            $tblProjetos = new Projetos();
            $tbAvaliacao = new AvaliacaoProposta();
            $tblUsuario = new Autenticacao_Model_Usuario();

            $movimentacoes = array();
            foreach ($rsPropostas as $proposta) {
                //Buscando movimenta��o desta proposta
                $rsMovimentacao = $tblMovimentacao->buscar(array("idprojeto = ?" => $proposta->idPreProjeto, "stestado = ?" => 0))->current();
                $movimentacoes[$proposta->idPreProjeto]["tecnico"] = "";

                if (count($rsMovimentacao)) {
                    //Descobrindo se esta proposta ja existe em projetos
                    $rsProjeto = $tblProjetos->buscar(array("idprojeto = ?" => $proposta->idPreProjeto));

                    //Descobrindo tecnico
                    $tecnico = $tblProposta->buscarConformidadeVisualTecnico($proposta->idPreProjeto);

                    $movimentacoes[$proposta->idPreProjeto]["codMovimentacao"] = $rsMovimentacao->Movimentacao;

                    if ($rsMovimentacao->Movimentacao == 95) {
                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#0000FF>Proposta com Proponente</font>";
                    } elseif ($rsMovimentacao->Movimentacao == 96) {
                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta em An�lise' . "</font>";

                        $rsAvaliacao = $tbAvaliacao->buscar(array("idProjeto = ?" => $proposta->idPreProjeto, "ConformidadeOK =?" => 9, "stEstado =?" => 0))->current();

                        if (count($rsAvaliacao) > 0) {
                            $rsUsuario = $tblUsuario->find($rsAvaliacao->idTecnico)->current();

                            if (count($rsUsuario) > 0) {
                                $usuarioNome = $rsUsuario->usu_nome;
                                $movimentacoes[$proposta->idPreProjeto]["tecnico"] = $usuarioNome;
                            }
                        }
                        //$movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#0000FF>Proposta com Proponente</font>";
                        /*if (!count($tecnico)>0)
                        {
                            $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta em An�lise' . "</font>";
                        }*/
                    } elseif ($rsMovimentacao->Movimentacao == 97 and (!count($rsProjeto) > 0)) {
                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta aguardando documentos' . "</font>";
                    } elseif (count($rsProjeto) > 0) {
                        $rsAvaliacao = $tbAvaliacao->buscar(array("idProjeto = ?" => $proposta->idPreProjeto, "ConformidadeOK =?" => 1, "stEstado =?" => 0))->current();
                        $rsUsuario = $tblUsuario->find($rsAvaliacao->idTecnico)->current();

                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "<font color=#FF0000>" . 'Proposta transformada em projeto' . "</font>";
                        if (count($rsUsuario) > 0) {
                            $movimentacoes[$proposta->idPreProjeto]["tecnico"] = $rsUsuario->usu_nome;
                        }
                    } else {
                        $usuarioNome = "";
                        $tipoUsuario = "";
                        $rsUsuario = null;

                        /*$rsUsuario = $tblUsuario->find($rsMovimentacao->Usuario)->current();
                        // Verificando se usuario e um coordenador
                        if(!empty($rsUsuario)>0){
                            if($tblUsuario->ECoordenador($rsUsuario->usu_codigo)){
                                $tipoUsuario = "Coordenador";
                            }else{
                                $tipoUsuario = "Analista";
                            }
                            $usuarioNome = $rsUsuario->usu_nome;
                        }*/

                        $rsAvaliacao = $tbAvaliacao->buscar(array("idProjeto = ?" => $proposta->idPreProjeto, "ConformidadeOK =?" => 1, "stEstado =?" => 0))->current();
                        if ($rsAvaliacao) {
                            $rsUsuario = $tblUsuario->find($rsAvaliacao->idTecnico)->current();
                        }

                        if ($rsMovimentacao->Movimentacao == 127) {
                            $tipoUsuario = "Coordenador";
                        } else {
                            $tipoUsuario = "Analista";
                        }

                        if (count($rsUsuario) > 0) {
                            $usuarioNome = $rsUsuario->usu_nome;
                        }

                        $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "Proposta com o {$tipoUsuario}";
                        $movimentacoes[$proposta->idPreProjeto]["tecnico"] = $usuarioNome;
                    }
                } else {
                    $movimentacoes[$proposta->idPreProjeto]["txtMovimentacao"] = "";
                }
            }
        }

        $arrDados = array(
            "propostas" => $rsPropostas,
            "agente" => $rsAgente,
            "movimentacoes" => $movimentacoes
        );

        $this->montaTela("admissibilidade/listarpropostasproponente.phtml", $arrDados);
    }

    public function listarPropostasAnaliseVisualTecnicoAction()
    {
        $post = Zend_Registry::get('post');
        $orgSuperior = $this->codOrgaoSuperior;
        $view = $this->getRequest()->getParam("view");

        if (!empty($view)) {
            $arrBusca = array("Tecnico " => "IS NOT NULL");
        } else {
            $arrBusca = array("idOrgao " => $orgSuperior);
        }
        if (is_numeric($post->avaliacao)) {
            $arrBusca["ConformidadeOK "] = "'$post->avaliacao'";
        }
        if (!empty($post->tecnico)) {
            $arrBusca["Tecnico "] = "'$post->tecnico'";
        }

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseVisualTecnico($arrBusca, array("Tecnico ASC"));

        $arrTecnicosPropostasReavaliacao = array();
        $arrTecnicosPropostasInicial = array();

        $arrTecnicos = array();
        foreach ($rsProposta as $proposta) {

            if ($proposta->ConformidadeOK == "0") {
                $arrTecnicosPropostasReavaliacao[$proposta->Tecnico][] = $proposta;
            }
            if ($proposta->ConformidadeOK == "9") {
                $arrTecnicosPropostasInicial[$proposta->Tecnico][] = $proposta;
            }
            //$arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $arrDados = array(
            "propostas" => $rsProposta,
            "tecnicosPropostasReavaliacao" => $arrTecnicosPropostasReavaliacao,
            "tecnicosPropostasInicial" => $arrTecnicosPropostasInicial,
            "urlXLS" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/xls-propostas-analise-visual-tecnico",
            "urlPDF" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/pdf-propostas-analise-visual-tecnico",
            "urlResumo" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/resumo-propostas-analise-visual-tecnico"
        );

        if (!empty($view)) {
            $this->_helper->layout->disableLayout();

            $this->montaTela($this->getRequest()->getParam("view"), $arrDados);
        } else {
            $this->montaTela("admissibilidade/listarpropostasanalisevisualtecnico.phtml", $arrDados);
        }
    }

    public function listarPropostasAnaliseDocumentalTecnicoAction()
    {
        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseDocumentalTecnico(array("sac.dbo.fnIdOrgaoSuperiorAnalista(a.idTecnico) =" => $usuario, "ConformidadeOK = " => 1), array("Tecnico ASC"));

        $arrTecnicosPropostas = array();
        $idDoc = 0;
        $nomeTec = "";
        foreach ($rsProposta as $proposta) {
            if ($proposta->CodigoDocumento != $idDoc || $proposta->Tecnico != $nomeTec) {
                $arrTecnicosPropostas[$proposta->Tecnico][$proposta->NomeProjeto][] = $proposta;
                $idDoc = $proposta->CodigoDocumento;
                $nomeTec = $proposta->Tecnico;
            }
        }

        $arrDados = array(
            "propostas" => $rsProposta,
            "tecnicosPropostas" => $arrTecnicosPropostas,
            "urlXLS" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/xls-propostas-analise-visual-tecnico",
            "urlPDF" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/pdf-propostas-analise-visual-tecnico"
        );

        $this->montaTela("admissibilidade/listarpropostasanalisedocumentaltecnico.phtml", $arrDados);
    }

    public function listarPropostasAnaliseFinalAction()
    {
        throw new Exception('Funcionalidade descontinuada na versao atual do Salic');
        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseFinal(array("idOrgao " => $usuario), array("Tecnico ASC"));

        $arrTecnicosPropostas = array();
        foreach ($rsProposta as $proposta) {
            $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $arrDados = array(
            "propostas" => $rsProposta,
            "tecnicosPropostas" => $arrTecnicosPropostas,
            "urlXLS" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/xls-propostas-analise-final",
            "urlPDF" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/pdf-propostas-analise-final",
            "urlResumo" => $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-proposta-analise-final"
        );

        $this->montaTela("admissibilidade/listarpropostasanalisefinal.phtml", $arrDados);
    }

    public function xlsPropostasAnaliseFinalAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseFinal(array("idOrgao " => $usuario), array("Tecnico ASC"));

        $html = "<table>
                <tr>
                    <td>Nr. Proposta</td>
                    <td>Nome da Proposta</td>
                    <td>Dt.Movimenta��o</td>
                </tr>
                ";
        foreach ($rsProposta as $proposta) {
            $html .= "<tr><td>{$proposta->idPreProjeto}</td>";
            $html .= "<td>{$proposta->NomeProjeto}</td>";
            $html .= "<td>{$proposta->DtMovimentacao}</td></tr>";
        }
        $html .= "</table>";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: inline; filename=file.ods;");
        echo $html;
    }

    public function xlsPropostasAnaliseVisualTecnicoAction()
    {
        set_time_limit(320);

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseVisualTecnico(array("idOrgao " => $usuario), array("Tecnico ASC"));

        $html = "<table>
                <tr>
                    <td>Nr. Proposta</td>
                    <td>Nome da Proposta</td>
                    <td>Dt.Movimenta��o</td>
                </tr>
                ";
        foreach ($rsProposta as $proposta) {
            $html .= "<tr><td>{$proposta->idProjeto}</td>";
            $html .= "<td>{$proposta->NomeProjeto}</td>";
            $html .= "<td>{$proposta->DtMovimentacao}</td></tr>";
        }
        $html .= "</table>";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: inline; filename=file.ods;");
        echo $html;
    }

    public function pdfPropostasAnaliseFinalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseFinal(array("idOrgao " => $usuario), array("Tecnico ASC"));

        $arrTecnicos = array();
        foreach ($rsProposta as $proposta) {
            $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $html = '
                <table width="100%">
                    <tr>
                        <th style="font-size:36px;">
                            Proposta em an�lise final
                        </th>
                    </tr>
                ';
        $ultimoRegistro = null;
        $ct = 1;
        if (!empty($arrTecnicosPropostas)) {
            foreach ($arrTecnicosPropostas as $tecnico => $propostas) {
                $html .= '
                    <tr>
                        <td align="left" style="border:1px #000000 solid; font-size:14px; font-weight:bold;">
                            Analista : ' . $tecnico . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" cellspacing="2" style="border:1px #000000 solid;">
                                <tr>
                                    <th width="15%" style="border-bottom:1px #000000 solid;">Nr. Proposta</th>
                                    <th width="65%" style="border-bottom:1px #000000 solid;">Nome da Proposta</th>
                                    <th width="20%" style="border-bottom:1px #000000 solid;">Dt. Movimenta��o</th>
                                </tr>
                ';
                foreach ($propostas as $proposta) {
                    $html .= '
                                <tr>
                                    <td align="center" style="font-size:12px;">' . $proposta->idPreProjeto . '</td>
                                    <td align="center" style="font-size:12px;">' . $proposta->NomeProjeto . '</td>
                                    <td align="center" style="font-size:12px;">' . ConverteData($proposta->DtMovimentacao, 5) . '</td>
                                </tr>
                ';
                }
                $html .= '
                            </table>
                        </td>
                    </tr>
                ';
                $ct++;
            }
        }

        $html .= '
                </table>
                ';
        $pdf = new PDF($html, 'pdf');
        $pdf->gerarRelatorio();

    }

    public function pdfPropostasAnaliseVisualTecnicoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        set_time_limit(320);

        $usuario = $this->codOrgaoSuperior;

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsProposta = $tblProposta->buscarPropostaAnaliseVisualTecnico(array("idOrgao " => $usuario), array("Tecnico ASC"));

        $arrTecnicos = array();
        foreach ($rsProposta as $proposta) {
            $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
        }

        $html = '
                <table width="100%">
                    <tr>
                        <th style="font-size:36px;">
                            Avalia��o: Reavalia��o
                        </th>
                    </tr>
                ';
        $ultimoRegistro = null;
        $ct = 1;
        if (!empty($arrTecnicosPropostas)) {
            foreach ($arrTecnicosPropostas as $tecnico => $propostas) {
                $html .= '
                    <tr>
                        <td align="left" style="border:1px #000000 solid; font-size:14px; font-weight:bold;">
                            Analista : ' . $tecnico . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" cellspacing="2" style="border:1px #000000 solid;">
                                <tr>
                                    <th width="15%" style="border-bottom:1px #000000 solid;">Nr. Proposta</th>
                                    <th width="65%" style="border-bottom:1px #000000 solid;">Nome da Proposta</th>
                                    <th width="20%" style="border-bottom:1px #000000 solid;">Dt. Movimenta��o</th>
                                </tr>
                ';
                foreach ($propostas as $proposta) {
                    if ($proposta->ConformidadeOK == 0) {
                        $html .= '
                                <tr>
                                    <td align="center" style="font-size:12px;">' . $proposta->idProjeto . '</td>
                                    <td align="center" style="font-size:12px;">' . $proposta->NomeProjeto . '</td>
                                    <td align="center" style="font-size:12px;">' . ConverteData($proposta->DtMovimentacao, 5) . '</td>
                                </tr>
                ';
                    }
                }
                $html .= '
                            </table>
                        </td>
                    </tr>
                ';
                $ct++;
            }
        }

        $html .= '
                </table>
                ';

        $html .= '
                <table width="100%">
                    <tr>
                        <th style="font-size:36px;">
                            Avalia��o: Inicial
                        </th>
                    </tr>
                ';
        $ultimoRegistro = null;
        $ct = 1;
        if (!empty($arrTecnicosPropostas)) {
            foreach ($arrTecnicosPropostas as $tecnico => $propostas) {
                $html .= '
                    <tr>
                        <td align="left" style="border:1px #000000 solid; font-size:14px; font-weight:bold;">
                            Analista : ' . $tecnico . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" cellspacing="2" style="border:1px #000000 solid;">
                                <tr>
                                    <th width="15%" style="border-bottom:1px #000000 solid;">Nr. Proposta</th>
                                    <th width="65%" style="border-bottom:1px #000000 solid;">Nome da Proposta</th>
                                    <th width="20%" style="border-bottom:1px #000000 solid;">Dt. Movimenta��o</th>
                                </tr>
                ';
                foreach ($propostas as $proposta) {
                    if ($proposta->ConformidadeOK == 9) {
                        $html .= '
                                <tr>
                                    <td align="center" style="font-size:12px;">' . $proposta->idProjeto . '</td>
                                    <td align="center" style="font-size:12px;">' . $proposta->NomeProjeto . '</td>
                                    <td align="center" style="font-size:12px;">' . ConverteData($proposta->DtMovimentacao, 5) . '</td>
                                </tr>
                ';
                    }
                }
                $html .= '
                            </table>
                        </td>
                    </tr>
                ';
                $ct++;
            }
        }

        $html .= '
                </table>
                ';
        $pdf = new PDF($html, 'pdf');
        $pdf->gerarRelatorio();

    }

    public function historicoAnaliseVisualAction()
    {
        throw new Exception('Funcionalidade descontinuada na versao atual do Salic');
        $post = Zend_Registry::get("get");
        $usuario = $this->codOrgaoSuperior;

        if (empty($post->busca)) {
            $tblProposta = new Proposta_Model_DbTable_PreProjeto();
            $rsTecnicos = $tblProposta->buscarTecnicosHistoricoAnaliseVisual($usuario);

            $arrDados = array(
                "tecnicos" => $rsTecnicos,
                "urlForm" => $this->view->baseUrl() . "/admissibilidade/admissibilidade/historico-analise-visual"
            );

            $this->montaTela("admissibilidade/consultarhistoricoanalisevisual.phtml", $arrDados);
        } else {
            $tecnico = ($post->tecnico != "") ? $post->tecnico : null;
            $dtInicio = ($post->dataPropostaInicial != "") ? ConverteData($post->dataPropostaInicial, 13) : null;
            $dtFim = ($post->dataPropostaFinal != "") ? ConverteData($post->dataPropostaFinal, 13) : null;

            $situacao = (!empty($post->situacao)) ? $post->situacao : null;

            $tblProposta = new Proposta_Model_DbTable_PreProjeto();
            $rsProposta = $tblProposta->buscarHistoricoAnaliseVisual($usuario, $tecnico, $situacao, $dtInicio, $dtFim);

            $arrTecnicosPropostas = array();
            foreach ($rsProposta as $proposta) {
                $arrTecnicosPropostas[$proposta->Tecnico][] = $proposta;
            }

            $arrDados = array(
                "propostas" => $rsProposta,
                "tecnicosPropostas" => $arrTecnicosPropostas,
                "urlResumo" => $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-historico-analise-visual"
            );

            $this->montaTela("admissibilidade/listarhistoricoanalisevisual.phtml", $arrDados);
        }
    }

    public function resumoHistoricoAnaliseVisualAction()
    {

        $arrDados = array(
            "resumo" => $_POST,
            "urlGerarGrafico" => $this->_urlPadrao . "/admissibilidade/admissibilidade/grafico-historico-analise-visual"
        );
        $this->montaTela("admissibilidade/resumohistoricoanalisevisual.phtml", $arrDados);
    }

    public function graficoHistoricoAnaliseVisualAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Resumo", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach ($_POST as $chave => $valor) {
            $aux = explode("gVal_", $chave);
            if (isset($aux[1])) {
                $titulos[] = str_replace("_", " ", $aux[1]);
                $valores[] = $valor;
            }
        }
        if (count($valores) > 0) {
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        } else {
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gr�fico.";
        }
    }

    public function avaliacaoHistoricoAnaliseVisualAction()
    {
        $get = Zend_Registry::get("get");
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();
        $rsAvaliacao = $tblProposta->buscarAvaliacaoHistoricoAnaliseVisual($get->idAvaliacao);

        $avaliacao = trim($rsAvaliacao[0]->Avaliacao);
        if (!empty ($avaliacao)) {
            echo $rsAvaliacao[0]->Avaliacao;
        } else {
            echo "<br><br><p align='center'><font color='red'><b>Nenhuma avalia&ccedil;&atilde;o encontrada.</b></font></p>";
        }
    }

    /**
     * ListarPropostas que vão ser avaliadas pelos tecnicos e Coordenadores de Admissibilidade.
     * A proposta são divididas em 2 areas SAV e SEFIC.
     * Tecnico só pode ver a suas proprias propostas.
     *
     * @access public
     * @return void
     */
    public function listarPropostasAction()
    {
        $post = Zend_Registry::get("post");

        $vwPainelAvaliar = new Admissibilidade_Model_DbTable_VwPainelAvaliarPropostas();

        if (Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE == $this->codGrupo){
            $where['idUsuario = ?'] = $this->idUsuario;
        }

        $orgao = new Orgaos();
        $orgao = $orgao->codigoOrgaoSuperior($this->codOrgao);
        $orgaoSuperior = $orgao[0]['Superior'];

        $where['idSecretaria = ?'] = $orgaoSuperior;
        $this->view->propostas = $vwPainelAvaliar->propostas($where, array("DtAvaliacao DESC"));
        $this->view->codGrupo = $this->codGrupo;

        $arrDados = array(
            "orgao" => $rsOrgao,
            "grupo" => $this->codGrupo,
            "formularioLocalizar" => $this->_urlPadrao . "/admissibilidade/admissibilidade/localizar",
            "urlResumo" => $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-propostas"
        );

        $this->montaTela("admissibilidade/listarpropostas.phtml", $arrDados);
    }

    public function listarPropostasNaoEnviadasAction()
    {
        $pag = 1;
        $get = Zend_Registry::get('get');
        if (isset($get->pag)) $pag = $get->pag;
        if (isset($get->tamPag)) $this->intTamPag = $get->tamPag;
        $inicio = ($pag > 1) ? ($pag - 1) * $this->intTamPag : 0;
        $fim = $inicio + $this->intTamPag;

        $rsPropostasNaoEnviadas = array();

        $tblProposta = new Proposta_Model_DbTable_PreProjeto();

        // =========== PROPOSTAS NAO ENVIADAS AO MINC AINDA =======================
        $arrBusca['m.Movimentacao = ?'] = 95;
        $rsPropostasNaoEnviadas = $tblProposta->buscarPropostaAdmissibilidadeZend($arrBusca, array("idProjeto DESC"), $this->intTamPag, $inicio); //m.Movimentacao = 95 >> NAO ENVIADA

        $total = $tblProposta->_totalRegistros;

        if ($fim > $total) $fim = $total;
        $totalPag = (int)(($total % $this->intTamPag == 0) ? ($total / $this->intTamPag) : (($total / $this->intTamPag) + 1));

        $arrDados = array(
            "pag" => $pag,
            "total" => $total,
            "inicio" => ($inicio + 1),
            "fim" => $fim,
            "totalPag" => $totalPag,
            "propostasNaoEnviadas" => $rsPropostasNaoEnviadas,
            "formularioLocalizar" => $this->_urlPadrao . "/admissibilidade/admissibilidade/localizar",
            "urlResumo" => $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-propostas",
            "urlPaginacao" => $this->_urlPadrao . "/admissibilidade/admissibilidade/listar-propostas-nao-enviadas?"
        );

        $this->montaTela("admissibilidade/listarpropostasnaoenviadas.phtml", $arrDados);
    }

    public function resumoPropostasAction()
    {
        $arrDados = array(
            "resumo" => $_POST,
            "urlGerarGrafico" => $this->_urlPadrao . "/admissibilidade/admissibilidade/grafico-admissibilidade-propostas"
        );
        $this->montaTela("admissibilidade/resumopropostas.phtml", $arrDados);
    }

    public function resumoDistribuicaoPropostasAction()
    {
        if ($this->codOrgao) {
            $params = new stdClass();
            $params->usu_orgao = $this->codOrgao;
            $analistas = AdmissibilidadeDAO::consultarRedistribuirAnalise($params);
            $this->view->analistas = array();
            $this->view->urlResumo = $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-distribuicao-propostas";
            $i = 0;

            foreach ($analistas as $analista) {
                $dados[$analista->Tecnico][$analista->Fase][$i]['nrProposta'] = $analista->idProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['NomeProjeto'] = $analista->NomeProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['DtMovimentacao'] = $analista->DtMovimentacao;
                $dados[$analista->Tecnico][$analista->Fase][$i]['fase'] = $analista->Fase;
                $i++;
            }
        }

        $arrDados = array(
            "resumo" => $_POST,
            "analistas" => $dados,
            "urlGerarGrafico" => $this->_urlPadrao . "/admissibilidade/admissibilidade/grafico-distribuicao-propostas"
        );
        $this->montaTela("admissibilidade/resumodistribuicaopropostas.phtml", $arrDados);
    }

    public function resumoGerenciamentoPropostaAction()
    {

        $arrDados = array(
            "resumo" => $_POST,
            "urlGerarGrafico" => $this->_urlPadrao . "/admissibilidade/admissibilidade/grafico-gerenciamento-propostas"
        );
        $this->montaTela("admissibilidade/resumogerenciamentopropostas.phtml", $arrDados);
    }

    public function resumoPropostaAnaliseFinalAction()
    {

        $arrDados = array(
            "resumo" => $_POST,
            "urlGerarGrafico" => $this->_urlPadrao . "/admissibilidade/admissibilidade/grafico-propostas-analise-final"
        );
        $this->montaTela("admissibilidade/resumopropostasanalisefinal.phtml", $arrDados);
    }

    public function resumoPropostasAnaliseVisualTecnicoAction()
    {

        if (!$_POST) {
            $this->_redirect("/admissibilidade/admissibilidade/listar-propostas-analise-visual-tecnico");
        }
        //x($_POST);
        $arrReavaliacao = array();
        $arrInicial = array();
        //prepara dados para gerar grafico
        foreach ($_POST as $analista => $qtde) {
            $arrTempReavaliacao = explode("reavaliacao_", $analista);
            if (isset($arrTempReavaliacao[1])) {
                $arrReavaliacao[str_replace("_", " ", $arrTempReavaliacao[1])] = $qtde;
            }

            $arrTempInicial = explode("inicial_", $analista);
            if (isset($arrTempInicial[1])) {
                $arrInicial[str_replace("_", " ", $arrTempInicial[1])] = $qtde;
            }
        }
        $arrDados = array(
            "resumoReavaliacao" => $arrReavaliacao,
            "resumoInicial" => $arrInicial,
            "urlGerarGrafico" => $this->_urlPadrao . "/admissibilidade/admissibilidade/grafico-proposta-analise-visual-tecnico"
        );
        $this->montaTela("admissibilidade/resumopropostaanalisevisualtecnico.phtml", $arrDados);
    }

    public function graficoDistribuicaoPropostasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        error_reporting(E_ERROR);

        if ($this->codOrgao) {
            $params = new stdClass();
            $params->usu_orgao = $this->codOrgao;
            $analistas = AdmissibilidadeDAO::consultarRedistribuirAnalise($params);
            $this->view->analistas = array();
            $this->view->urlResumo = $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-distribuicao-propostas";
            $i = 0;
            foreach ($analistas as $analista) {
                $dados[$analista->Tecnico][$analista->Fase][$i]['nrProposta'] = $analista->idProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['NomeProjeto'] = $analista->NomeProjeto;
                $dados[$analista->Tecnico][$analista->Fase][$i]['DtMovimentacao'] = $analista->DtMovimentacao;
                $dados[$analista->Tecnico][$analista->Fase][$i]['fase'] = $analista->Fase;
                $i++;
            }
        }

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Avalia&ccedil;&atilde;o", "Registros");

        $grafico->configurar($_POST);

        foreach ($dados as $nomeAnalista => $fases) {
            if (isset($_POST["todos"]) || isset($_POST[str_replace(".", "_", str_replace(" ", "_", $nomeAnalista))])) {
                foreach ($fases as $faseNome => $faseItems) {
                    $arrSeries[] = $faseNome;
                }
            }
        }

        $arrSeries = array_unique($arrSeries);
        $aux = array();
        foreach ($dados as $nomeAnalista => $fases) {
            if (isset($_POST["todos"]) || isset($_POST[str_replace(".", "_", str_replace(" ", "_", $nomeAnalista))])) {
                foreach ($fases as $faseNome => $faseItems) {
                    $valores[] = count($faseItems);
                    $titulos[] = $faseNome;
                }
                if (count($arrSeries) != count($valores)) {
                    $valores[] = 0;
                }

                $grafico->addDados($valores, $nomeAnalista);
                $valores = array();
            }
        }

        /*$titulos = array("dan","dan1","dan2","dan3","dan4");
        $valores = array(1,2,3,4,5);
        $valores2 = array(1,2,3,4,5);
        $grafico->addDados($valores,"visual");
        $grafico->addDados($valores2, "documental");*/
        $grafico->setTituloItens(array_unique($titulos));
        $grafico->gerar();
    }

    public function graficoAdmissibilidadePropostasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Avaliacao", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach ($_POST as $chave => $valor) {
            $aux = explode("gVal_", $chave);
            if (isset($aux[1])) {
                $titulos[] = $aux[1];
                $valores[] = $valor;
            }
        }

        if (count($valores) > 0) {
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        } else {
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gr�fico.";
        }
    }

    public function graficoGerenciamentoPropostasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("T�cnicos", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach ($_POST as $chave => $valor) {
            $aux = explode("gVal_", $chave);
            if (isset($aux[1])) {
                $titulos[] = str_replace("_", " ", $aux[1]);
                $valores[] = $valor;
            }
        }
        if (count($valores) > 0) {
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        } else {
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gr�fico.";
        }
    }

    public function graficoPropostaAnaliseVisualTecnicoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        error_reporting(E_ERROR);
        if (!$_POST) {
            $this->_redirect("/admissibilidade/admissibilidade/resumo-propostas-analise-visual-tecnico");
        }

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Avalia��o", "Registros");
        $grafico->configurar($_POST);

        $analista = array();
        $aux = array();
        foreach ($_POST as $nomeAnalista => $qtde) {

            if (isset($_POST["todos"])) {
                $nomeReavaliacao = explode("gValReavaliacao_", $nomeAnalista);
                $nomeInicial = explode("gValInicial_", $nomeAnalista);

                if (isset($nomeReavaliacao[1])) {
                    $analista[] = $nomeReavaliacao[1];
                } elseif (isset($nomeInicial[1])) {
                    $analista[] = $nomeInicial[1];
                }
            } elseif (isset($_POST["reavaliacao"])) {
                $nomeReavaliacao = explode("gValReavaliacao_", $nomeAnalista);
                if (isset($nomeReavaliacao[1])) {
                    $analista[] = $nomeReavaliacao[1];
                }
            } elseif (isset($_POST["inicial"])) {
                $nomeInicial = explode("gValInicial_", $nomeAnalista);
                if (isset($nomeInicial[1])) {
                    $analista[] = $nomeInicial[1];
                }
            }/*elseif(isset($_POST[$nomeAnalista])){
                $chaves = array_keys($_POST);
                $analista[]=$chaves[0];
                break;
            }*/

            /*if(isset($nomeInicial[1])){
                $analista[] = str_replace("_", " ", $nomeInicial[1]);
                $valores[$nomeInicial[1]][] = $qtde;
                $valores2[] = $qtde;
            }else{
                $valores[$aux1[1]][] = 0;
            }*/
        }

        $analista = array_unique($analista);
        foreach ($analista as $nome) {
            if (isset($_POST["todos"])) {

                if (array_key_exists("gValReavaliacao_" . $nome, $_POST) && array_key_exists("gValInicial_" . $nome, $_POST)) {

                    $valores[] = $_POST["gValReavaliacao_" . $nome];
                    $valores[] = $_POST["gValInicial_" . $nome];

                } elseif (array_key_exists("gValReavaliacao_" . $nome, $_POST) && !array_key_exists("gValInicial_" . $nome, $_POST)) {
                    $valores[] = $_POST["gValReavaliacao_" . $nome];
                    $valores[] = 0;

                } elseif (!array_key_exists("gValReavaliacao_" . $nome, $_POST) && array_key_exists("gValInicial_" . $nome, $_POST)) {
                    $valores[] = 0;
                    $valores[] = $_POST["gValInicial_" . $nome];

                } else {
                    $valores[] = 0;
                    $valores[] = 0;
                }
            } elseif (isset($_POST["reavaliacao"])) {

                $valores[] = $_POST["gValReavaliacao_" . $nome];

            } elseif (isset($_POST["inicial"])) {

                $valores[] = $_POST["gValInicial_" . $nome];
            }

            $grafico->addDados($valores, str_replace("_", " ", $nome));
            $valores = array();
        }

        $arrTitulo = array();
        if (isset($_POST["reavaliacao"])) {
            $arrTitulo[] = "Reavalia��o";
        } elseif (isset($_POST["inicial"])) {
            $arrTitulo[] = "Inicial";
        } else {
            $arrTitulo[] = "Reavalia��o";
            $arrTitulo[] = "Inicial";
        }

        $grafico->setTituloItens($arrTitulo);
        $grafico->gerar();
    }

    public function graficoPropostasAnaliseFinalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $grafico = new Grafico($_POST["cgTipoGrafico"]);
        $grafico->setTituloGrafico("Registros");
        $grafico->setTituloEixoXY("Resumo", "Registros");
        $grafico->configurar($_POST);

        $aux = array();
        $valores = array();
        foreach ($_POST as $chave => $valor) {
            $aux = explode("gVal_", $chave);
            if (isset($aux[1])) {
                $titulos[] = str_replace("_", " ", $aux[1]);
                $valores[] = $valor;
            }
        }
        if (count($valores) > 0) {
            $grafico->addDados($valores);
            $grafico->setTituloItens($titulos);
            $grafico->gerar();
        } else {
            echo "Nenhum dado encontrado gera&ccedil;&atilde;o de Gr�fico.";
        }
    }

    public function localizarAction()
    {
        throw new Exception("Metodo descontinuado nesta versão");
        $arrDados = array(
            "urlAcao" => $this->_urlPadrao . "/admissibilidade/admissibilidade/listar-propostas"
        );

        $this->montaTela("admissibilidade/localizarpropostas.phtml", $arrDados);
    }


    public function localizarGerenciamentoPropostaAction()
    {
        $params = new stdClass();
        $params->usu_nome = "";
        $params->gru_codigo = $_SESSION['GrupoAtivo']['codOrgao'];
        $params->usu_orgao = $this->codOrgaoSuperior;
        $this->view->novosAnalistas = AdmissibilidadeDAO::consultarRedistribuirAnaliseItemSelect($params);
        $arrDados = array(
            "urlAcao" => $this->_urlPadrao . "/admissibilidade/admissibilidade/gerenciamentodepropostas",
            "urlResumo" => $this->_urlPadrao . "/admissibilidade/admissibilidade/resumo-gerenciamento-proposta"
        );

        $this->montaTela("admissibilidade/localizarpropostasgerenciamento.phtml", $arrDados);
    }

    public function desarquivarpropostasAction()
    {

    }

    public function buscarPropostaAction()
    {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $nrProposta = $_POST['nrProposta'];

        $dados = array();
        if (!empty($nrProposta)) {
            $dados['idPreProjeto = ?'] = $nrProposta;
            $dados['stEstado = ?'] = 0;
            $dados['dtArquivamento is not null'] = '';
            $PreProjeto = new Proposta_Model_DbTable_PreProjeto();
            $result = $PreProjeto->buscar($dados);

            $a = 0;
            if (count($result) > 0) {
                foreach ($result as $registro) {
                    $dadosProposta[$a]['idPreProjeto'] = $registro['idPreProjeto'];
                    $dadosProposta[$a]['NomeProjeto'] = utf8_encode($registro['NomeProjeto']);
                    $a++;
                }
                $jsonEncode = json_encode($dadosProposta);
                echo json_encode(array('resposta' => true, 'conteudo' => $dadosProposta));

            } else {
                echo json_encode(array('resposta' => false));
            }

        } else {
            echo json_encode(array('resposta' => false));
        }
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    public function desarquivamentoPropostaAction()
    {
        $post = Zend_Registry::get('post');

        if ($post->desarquivamento) {

            $dados = array(
//                'DtArquivamento' => new Zend_Db_Expr('GETDATE()'),
                'DtArquivamento' => null,
                'stEstado' => 1
            );
            $where = array('idPreProjeto = ?' => $post->nrProposta);

            $PreProjeto = new Proposta_Model_DbTable_PreProjeto();
            $result = $PreProjeto->update($dados, $where);

            parent::message("Proposta desarquivada com sucesso!", "/admissibilidade/admissibilidade/desarquivarpropostas", "CONFIRM");

        } else {
            parent::message("Erro ao desarquivar proposta!", "/admissibilidade/admissibilidade/desarquivarpropostas", "ERROR");
        }
    }

    public function painelProjetosDistribuidosAction()
    {
        throw new Exception('Funcionalidade descontinuada na versao atual do Salic');
        //select codigo,sigla from orgaos WHERE Status = 0 and vinculo = 1 order by sigla
        $where = array(
            'Status = ?' => 0,
            'vinculo = ?' => 1,
        );

        $Orgaos = new Orgaos();
        $dados = $Orgaos->buscar($where, array('Sigla'));
        $this->view->orgaos = $dados;
    }

    public function listaProjetosDistribuidosAction()
    {

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if ($this->_request->getParam("qtde")) {
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if ($this->_request->getParam("ordem")) {
            $ordem = $this->_request->getParam("ordem");
            if ($ordem == "ASC") {
                $novaOrdem = "DESC";
            } else {
                $novaOrdem = "ASC";
            }
        } else {
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if ($this->_request->getParam("campo")) {
            $campo = $this->_request->getParam("campo");
            $order = array($campo . " " . $ordem);
            $ordenacao = "&campo=" . $campo . "&ordem=" . $ordem;

        } else {
            $campo = null;
            $order = array(2, 5, 6); //Pronac,Produto,DescricaoAnalise
            $ordenacao = null;
        }

        $pag = 1;
        $get = Zend_Registry::get('get');

        if (isset($get->pag)) $pag = $get->pag;
        $inicio = ($pag > 1) ? ($pag - 1) * $this->intTamPag : 0;

        /* ================== PAGINACAO ======================*/
        $where = array();

        if (isset($get->pronac) && !empty($get->pronac)) {
            $where['Pronac = ?'] = $get->pronac;
            $this->view->pronac = $get->pronac;
        }

        if (isset($get->estado) && !empty($get->estado)) {
            if ($get->estado == 1) {
                $situacao = 'Em an�lise';
            } else {
                $situacao = '<font color=red>Concluida</font>';
            }
            $where['Situacao = ?'] = $situacao;
            $this->view->estado = $get->estado;
        }

        if (isset($get->orgao) && !empty($get->orgao)) {
            $where['idOrgao = ?'] = $get->orgao;
            $this->view->orgao = $get->orgao;
        }

        $vwProjetoDistribuidoVinculada = New vwProjetoDistribuidoVinculada();
        $total = $vwProjetoDistribuidoVinculada->buscarUnidades($where, $order, null, null, true);
        $fim = $inicio + $this->intTamPag;

        $totalPag = (int)(($total % $this->intTamPag == 0) ? ($total / $this->intTamPag) : (($total / $this->intTamPag) + 1));
        $tamanho = ($fim > $total) ? $total - $inicio : $this->intTamPag;

        $busca = $vwProjetoDistribuidoVinculada->buscarUnidades($where, $order, $tamanho, $inicio);

        $paginacao = array(
            "pag" => $pag,
            "qtde" => $this->intTamPag,
            "campo" => $campo,
            "ordem" => $ordem,
            "ordenacao" => $ordenacao,
            "novaOrdem" => $novaOrdem,
            "total" => $total,
            "inicio" => ($inicio + 1),
            "fim" => $fim,
            "totalPag" => $totalPag,
            "Itenspag" => $this->intTamPag,
            "tamanho" => $tamanho
        );

        $this->view->paginacao = $paginacao;
        $this->view->qtdDocumentos = $total;
        $this->view->dados = $busca;
        $this->view->intTamPag = $this->intTamPag;
    }

    public function imprimirProjetosDistribuidosAction()
    {

        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout
        $post = Zend_Registry::get('post');

        //DEFINE PARAMETROS DE ORDENACAO / QTDE. REG POR PAG. / PAGINACAO
        if ($this->_request->getParam("qtde")) {
            $this->intTamPag = $this->_request->getParam("qtde");
        }
        $order = array();

        //==== parametro de ordenacao  ======//
        if ($this->_request->getParam("ordem")) {
            $ordem = $this->_request->getParam("ordem");
            if ($ordem == "ASC") {
                $novaOrdem = "DESC";
            } else {
                $novaOrdem = "ASC";
            }
        } else {
            $ordem = "ASC";
            $novaOrdem = "ASC";
        }

        //==== campo de ordenacao  ======//
        if ($this->_request->getParam("campo")) {
            $campo = $this->_request->getParam("campo");
            $order = array($campo . " " . $ordem);
            $ordenacao = "&campo=" . $campo . "&ordem=" . $ordem;
        } else {
            $campo = null;
            $order = array(2, 5, 6); //Pronac,Produto,DescricaoAnalise
            $ordenacao = null;
        }

        /* ================== PAGINACAO ======================*/
        $where = array();

        if (isset($post->pronac) && !empty($post->pronac)) {
            $where['Pronac = ?'] = $post->pronac;
            $this->view->pronac = $post->pronac;
        }

        if (isset($post->estado) && !empty($post->estado)) {
            if ($post->estado == 1) {
                $situacao = 'Em an�lise';
            } else {
                $situacao = '<font color=red>Concluida</font>';
            }
            $where['Situacao = ?'] = $situacao;
            $this->view->estado = $post->estado;
        }

        if (isset($post->orgao) && !empty($post->orgao)) {
            $where['idOrgao = ?'] = $post->orgao;
            $this->view->orgao = $post->orgao;
        }

        $vwProjetoDistribuidoVinculada = New vwProjetoDistribuidoVinculada();
        $busca = $vwProjetoDistribuidoVinculada->buscarUnidades($where, $order);

        $this->view->dados = $busca;
    }

    /**
     * @todo: Testar e refatorar esse metodo.
     */
    private function incluirProjeto($idPreProjeto, $cnpjcpf, $idOrgao, $idUsuario, $nrProcesso, $stEstado) {

        $db= Zend_Db_Table::getDefaultAdapter();
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        try {
            $objInteressado = new Interessado();
            $arrayInteressados = $objInteressado->findAll(array('CgcCpf' => $cnpjcpf));

            if(!$arrayInteressados) {
                $sqlInteressado = "INSERT INTO SAC.dbo.Interessado (CgcCpf,TipoPessoa,Nome,Responsavel,Endereco,Cidade,UF,CEP,Natureza,Esfera,Administracao,Utilidade)
                                  SELECT TOP 1 p.CNPJCPF,
                                     case
                                       when len(p.CNPJCPF)=11
                                         then  '1'
                                         else  '2'
                                     end as TipoPessoa,
                                     Nome,
                                     SAC.dbo.fnNomeResponsavel(p.Usuario),
                                     p.Logradouro + ' - ' + p.Bairro,
                                     u.Municipio,
                                     u.UF,
                                     p.CEP,
                                     case
                                       when Direito = 1
                                         then '1'
                                       when Direito = 2 or Direito = 35
                                         then '2'
                                      end as Direito,
                                     case
                                       when Esfera = 3
                                         then '1'
                                       when Esfera = 4
                                         then '2'
                                       when Esfera = 5
                                         then '3'
                                      end as Esfera,
                                     case
                                       when Administracao = 11
                                         then '1'
                                       when Administracao = 12
                                         then '2'
                                      end as Administracao,
                                     case
                                       when Direito = 2
                                         then '1'
                                       when Direito = 35
                                         then '2'
                                      end as Utilidade
                                FROM SAC.dbo.vCadastrarProponente p
                               INNER JOIN Agentes.dbo.Agentes a on (p.idAgente = a.idAgente)
                               INNER JOIN Agentes.dbo.EnderecoNacional e on (p.idAgente = e.idAgente and e.Status = 1)
                               INNER JOIN Agentes.dbo.vUFMunicipio u on (e.UF = u.idUF and e.Cidade = u.idMunicipio )
                                LEFT JOIN  SAC.dbo.vwNatureza n on (p.idAgente =n.idAgente)
                               WHERE p.CNPJCPF='{$cnpjcpf}'
                                 AND Correspondencia = 1";
            } else {
                $sqlInteressado = "  UPDATE SAC.dbo.Interessado
                                        SET Endereco = e.Logradouro + ' - ' + e.Bairro,
                                            Cidade   = u.Municipio,
                                            UF       = u.UF,
                                            CEP      = e.CEP,
                                            Natureza  = case
                                                        when n.Direito = 1
                                                          then '1'
                                                        when n.Direito = 2 or n.Direito = 35
                                                          then '2'
                                                       end,
                                            Esfera   = case
                                                        when n.Esfera = 3
                                                          then '1'
                                                        when n.Esfera = 4
                                                          then '2'
                                                        when n.Esfera = 5
                                                         then '3'
                                                       end,
                                            Administracao = case
                                                             when n.Administracao = 11
                                                              then '1'
                                                             when n.Administracao = 12
                                                              then '2'
                                                            end,
                                            Utilidade = case
                                                         when n.Direito = 2
                                                           then '1'
                                                         when n.Direito = 35
                                                           then '2'
                                                        end
                                            FROM SAC.dbo.Interessado i
                                                 INNER JOIN Agentes.dbo.Agentes a on (i.CgcCpf = a.CNPJCPF)
                                                 INNER JOIN Agentes.dbo.EnderecoNacional e on (a.idAgente = e.idAgente and e.Status = 1)
                                                 INNER JOIN Agentes.dbo.vUFMunicipio u on (e.UF = u.idUF and e.Cidade = u.idMunicipio )
                                                  LEFT JOIN SAC.dbo.vwNatureza n on (a.idAgente =n.idAgente)
                                                 WHERE i.CGCCPF='{$cnpjcpf}' and e.Status = 1";
            }

            $resultado = $db->query($sqlInteressado);
            if($resultado->rowCount() < 1) {
                throw new Exception ("Erro ao tentar incluir ou alterar %d registros na tabela Interessado.");
            }

            $sqlSequencialProjetos = "UPDATE SAC.dbo.SequencialProjetos
                                         SET Sequencial = Sequencial + 1
                                       WHERE Ano = YEAR(GETDATE())";

            $resultado = $db->query($sqlSequencialProjetos);
            $ano = date('Y');
            if($resultado->rowCount() < 1) {
                $sqlSequencialProjetos = " INSERT INTO SAC.dbo.SequencialProjetos (Ano,Sequencial) VALUES ('{$ano}' ,1)";
                $resultado = $db->query($sqlSequencialProjetos);
                if($resultado->rowCount() < 1) {
                    throw new Exception ("Não é possível incluir ou alterar mais de um registro na tabela SequencialProjetos.");
                }
            }

            $sqlSequencial = "select Sequencial from sac.dbo.SequencialProjetos where ano = '{$ano}'";
            $objSequencial = $db->fetchRow($sqlSequencial);
            $sequencial = $objSequencial->Sequencial;
            $AnoProjeto = date("y");
            $NrProjeto = str_pad($sequencial, 4, "0", STR_PAD_LEFT);
            $situacaoProjeto = 'B11'; #atinga situacao B01
            $providenciaTomada = 'Proposta transformada em projeto cultural';

            $sqlProjetos = "INSERT INTO SAC.dbo.Projetos
                                  (AnoProjeto,Sequencial,UFProjeto,Area,Segmento,Mecanismo,NomeProjeto,CgcCpf,Situacao,DtProtocolo,DtAnalise,
                                   OrgaoOrigem,Orgao,DtSituacao,ProvidenciaTomada,ResumoProjeto,DtInicioExecucao,DtFimExecucao,SolicitadoReal,
                                   idProjeto,Processo,Logon)
                                SELECT TOP 1 '{$AnoProjeto}', '{$NrProjeto}', u.Sigla, SAC.dbo.fnSelecionarArea(idPreProjeto),SAC.dbo.fnSelecionarSegmento(idPreProjeto),
                                   Mecanismo, NomeProjeto, a.CNPJCPF, {$situacaoProjeto}, getdate(), getdate(), {$idOrgao}, {$idOrgao}, getdate(),
                                   {$providenciaTomada}, ResumoDoProjeto, DtInicioDeExecucao, DtFinalDeExecucao,
                                   SAC.dbo.fnSolicitadoNaProposta(idPreProjeto), idPreProjeto, '{$nrProcesso}', {$idUsuario}
                                   FROM SAC.dbo.PreProjeto p
                                   INNER JOIN Agentes.dbo.Agentes a on (p.idAgente = a.idAgente)
                                   INNER JOIN Agentes.dbo.EnderecoNacional e on (a.idAgente = e.idAgente and e.Status = 1)
                                   INNER JOIN Agentes.dbo.UF u on (e.UF = u.idUF)
                                   WHERE idPreProjeto  = {$idPreProjeto}
                                     AND NOT EXISTS(SELECT TOP 1 * FROM SAC.dbo.Projetos x WHERE p.idPreProjeto = x.idProjeto)";

            $resultado = $db->query($sqlProjetos);
            if($resultado->rowCount() < 1) {
                throw new Exception ("Não há registro para incluir / alterar registro na tabela Interessado");
            }

            $idPronac = $db->lastInsertId();

            if( $stEstado !== 610) {

                # CARREGA PLANILHA PARA O PARECERISTA -- tbPlanilhaProjeto
                $sqlParecerista = "INSERT INTO SAC.dbo.tbPlanilhaProjeto
                                     (idPlanilhaProposta,idPronac,idProduto,idEtapa,idPlanilhaItem,Descricao,idUnidade,Quantidade,Ocorrencia,ValorUnitario,QtdeDias,
                                     TipoDespesa,TipoPessoa,Contrapartida,FonteRecurso,UFDespesa,    MunicipioDespesa,idUsuario)
                                   SELECT idPlanilhaProposta, {$idPronac},idProduto,idEtapa,idPlanilhaItem,Descricao,Unidade,
                                        Quantidade, Ocorrencia,ValorUnitario,QtdeDias,TipoDespesa,TipoPessoa,Contrapartida,FonteRecurso,UFDespesa,
                                        MunicipioDespesa, 0
                                        FROM SAC.dbo.tbPlanilhaProposta
                                        WHERE idProjeto = {$idPreProjeto}";
                $resultado = $db->query($sqlParecerista);

                # CARREGA A TABELA DE ANÁLISE DE CONTEÚDO PARA O PARECERISTA  -- tbAnaliseDeConteudo
                $sqlAnaliseDeConteudo = "INSERT INTO SAC.dbo.tbAnaliseDeConteudo (idPronac,idProduto)
                                         SELECT {$idPronac},idProduto FROM SAC.dbo.tbPlanilhaProposta
                                          WHERE idProjeto = {$idPreProjeto} AND idProduto <> 0
                                          GROUP BY idProduto";
                $resultado = $db->query($sqlAnaliseDeConteudo);

                # CARREGA A TABELA DE TBDISTRIBUIRPARECER
                $sqlVinculada = "SELECT idOrgao as idVinculada 
                                    FROM sac.dbo.PlanoDistribuicaoProduto t 
                                    INNER JOIN vSegmento s on (t.Segmento = s.Codigo)
                                    WHERE t.stPrincipal = 1 and idProjeto = '{$idPreProjeto}'";
                $idVinculada = $db->fetchOne($sqlVinculada);

                $sqlDistribuirParecer = "INSERT INTO SAC.dbo.tbDistribuirParecer (idPronac,idProduto,TipoAnalise,idOrgao,DtEnvio, stPrincipal)
                                         SELECT {$idPronac},idProduto, 3,{$idVinculada},getdate(), stPrincipal FROM SAC.dbo.PlanoDistribuicaoProduto
                                          WHERE idProjeto = {$idPreProjeto}";
                $resultado = $db->query($sqlDistribuirParecer);

            }

            $sqlContaBancaria = "INSERT INTO SAC.dbo.ContaBancaria (AnoProjeto,Sequencial,Mecanismo,Banco,Agencia,Logon)
                                 SELECT '{$AnoProjeto}', '{$NrProjeto}', Mecanismo, '001', AgenciaBancaria, {$idUsuario}
                                   FROM SAC.dbo.PreProjeto
                                  WHERE idPreProjeto = {$idPreProjeto}";
            $resultado = $db->query($sqlContaBancaria);

            if($resultado->rowCount() < 1) {
                throw new Exception ("Não é possível incluir mais de %d registros na ContaBancaria");
            }

            $sqlHistoricoEmail = "SELECT TOP 1 * FROM SAC.dbo.tbHistoricoEmail WHERE idPronac = {$idPronac} and
                                  idTextoEmail = 12 and (CONVERT(char(10),(DtEmail),111) = CONVERT(char(10),getdate(),111))";
            $arrayHistoricoEmail = $db->fetchRow($sqlHistoricoEmail);
            if(!$arrayHistoricoEmail) {
                $idTextoEmail = 12;
                $sqlInsertHistoricoEmail = "INSERT INTO SAC.dbo.tbHistoricoEmail (idPronac,idTextoemail,DtEmail,stEstado,idUsuario)
                                            VALUES ({$idPronac}, {$idTextoEmail}, getdate(), 1, {$idUsuario})";

                $resultado = $db->query($sqlInsertHistoricoEmail);
                if($resultado->rowCount() < 1) {
                    throw new Exception ("Não é permitido inserir %d registros ao mesmo tempo na tabela tbHistoricoEmail");
                }

                $objTbTextoEmail = new tbTextoEmail();
                $resultadoTetoEmail = $objTbTextoEmail->obterTextoPorIdentificador($idTextoEmail);;

                $objProjetos = new Projetos();

                $resultadoMensagem = $objProjetos ->obterInteressadoProjeto($idPronac);

                $mensagemEmail = "<b>Projeto: {$AnoProjeto}{$NrProjeto} - {$resultadoMensagem->NomeProjeto} <br> Proponente: {$resultadoMensagem->Nome}<br> </b>{$resultadoTetoEmail->dsTexto}";

                $objInternet = new Agente_Model_DbTable_Internet();
                $arrayEmails = $objInternet->obterEmailProponentesPorPreProjeto($idPreProjeto);

                foreach($arrayEmails as $email) {
                    EmailDAO::enviarEmail($email->Descricao, "Projeto Cultural", $mensagemEmail);
                }
            }
        } catch (Exception $objException) {
            throw new Exception ($objException->getMessage(), 0, $objException);
        }

    }



    public function listarPropostasAjaxAction()
    {
        $start = $this->getRequest()->getParam('start');
        $length = $this->getRequest()->getParam('length');
        $draw = (int)$this->getRequest()->getParam('draw');
        $search = $this->getRequest()->getParam('search');
        $order = $this->getRequest()->getParam('order');
        $columns = $this->getRequest()->getParam('columns');

        $order = ($order[0]['dir'] != 1) ? array($columns[$order[0]['column']]['name'] . ' '. $order[0]['dir']) : array("DtAvaliacao DESC");

        $vwPainelAvaliar = new Admissibilidade_Model_DbTable_VwPainelAvaliarPropostas();

        if (Autenticacao_Model_Grupos::TECNICO_ADMISSIBILIDADE == $this->codGrupo){
            $where['idUsuario = ?'] = $this->idUsuario;
        }

        $orgao = new Orgaos();
        $orgao = $orgao->codigoOrgaoSuperior($this->codOrgao);
        $orgaoSuperior = $orgao[0]['Superior'];
        $where['idSecretaria = ?'] = $orgaoSuperior;

        $propostas = $vwPainelAvaliar->propostas($where, $order, $start, $length, $search);

        $recordsTotal = 0;
        $recordsFiltered = 0;

        if (!empty($propostas)) {
            $zDate = new Zend_Date();
            foreach($propostas as $key => $proposta){
                $zDate->set($proposta->DtMovimentacao);

                $proposta->NomeProposta = utf8_encode($proposta->NomeProposta);
                $proposta->Tecnico = utf8_encode($proposta->Tecnico);
                $proposta->DtMovimentacao= $zDate->toString('dd/MM/y h:m');
                $aux[$key] = $proposta;
            }

            $recordsTotal = $vwPainelAvaliar->propostasTotal($where);
            $recordsFiltered = $vwPainelAvaliar->propostasTotal($where, null, null, null, $search);
        }

        $this->_helper->json(array(
            "data" => !empty($aux) ? $aux : 0,
            'recordsTotal' => $recordsTotal ? $recordsTotal->total : 0,
            'draw' => $draw,
            'recordsFiltered' => $recordsFiltered ? $recordsFiltered->total : 0 ));
    }

    public function exibirpropostaculturalAjaxAction()
    {
        $this->_helper->layout->disableLayout(); // Desabilita o Zend Layout

        $idPreProjeto = $this->idPreProjeto;
        $dados = Proposta_Model_AnalisarPropostaDAO::buscarGeral($idPreProjeto);
        $this->view->itensGeral = $dados;

        $movimentacao = new Proposta_Model_DbTable_TbMovimentacao();
        $movimentacao = $movimentacao->buscarStatusAtualProposta($idPreProjeto);
        $this->view->movimentacao = $movimentacao['Movimentacao'];

        //========== inicio codigo dirigente ================
        $arrMandatos = array();
        $this->view->mandatos = $arrMandatos;
        $preProjeto = new Proposta_Model_DbTable_PreProjeto();
        $rsDirigentes = array();

        $Empresa = $preProjeto->buscar(array('idPreProjeto = ?' => $this->idPreProjeto))->current();
        $idEmpresa = $Empresa->idAgente;

        $Projetos = new Projetos();
        $dadosProjeto = $Projetos->buscar(array('idProjeto = ?' => $this->idPreProjeto))->current();

        // Busca na tabela apoio ExecucaoImediata stproposta
        $tableVerificacao = new Proposta_Model_DbTable_Verificacao();
        if( !empty($this->view->itensGeral[0]->stProposta))
            $this->view->ExecucaoImediata = $tableVerificacao->findBy(array('idVerificacao' => $this->view->itensGeral[0]->stProposta));

        $Pronac = null;
        if (count($dadosProjeto) > 0) {
            $Pronac = $dadosProjeto->AnoProjeto . $dadosProjeto->Sequencial;
        }
        $this->view->Pronac = $Pronac;

        if (isset($dados[0]->CNPJCPFdigirente) && $dados[0]->CNPJCPFdigirente != "") {
            $tblAgente = new Agente_Model_DbTable_Agentes();
            $tblNomes = new Nomes();
            foreach ($dados as $v) {
                $rsAgente = $tblAgente->buscarAgenteENome(array('CNPJCPF=?' => $v->CNPJCPFdigirente))->current();
                $rsDirigentes[$rsAgente->idAgente]['CNPJCPFDirigente'] = $rsAgente->CNPJCPF;
                $rsDirigentes[$rsAgente->idAgente]['idAgente'] = $rsAgente->idAgente;
                $rsDirigentes[$rsAgente->idAgente]['NomeDirigente'] = $rsAgente->Descricao;
            }

            $tbDirigenteMandato = new tbAgentesxVerificacao();
            foreach ($rsDirigentes as $dirigente) {
                $rsMandato = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idEmpresa, 'idDirigente = ?' => $dirigente['idAgente'], 'stMandato = ?' => 0));
                $NomeDirigente = $dirigente['NomeDirigente'];
                $arrMandatos[$NomeDirigente] = $rsMandato;
            }
        }

        $this->view->dirigentes = $rsDirigentes;
        $this->view->mandatos = $arrMandatos;
        //============== fim codigo dirigente ================

        $propostaPorEdital = false;
        if ($this->view->itensGeral[0]->idEdital && $this->view->itensGeral[0]->idEdital != 0) {
            $propostaPorEdital = true;
        }
        $this->view->isEdital = $propostaPorEdital;
        $this->view->itensTelefone = Proposta_Model_AnalisarPropostaDAO::buscarTelefone($this->view->itensGeral[0]->idAgente);
        $this->view->itensPlanosDistribuicao = Proposta_Model_AnalisarPropostaDAO::buscarPlanoDeDistribucaoProduto($idPreProjeto);
        $this->view->itensFonteRecurso = Proposta_Model_AnalisarPropostaDAO::buscarFonteDeRecurso($idPreProjeto);
        $this->view->itensLocalRealiazacao = Proposta_Model_AnalisarPropostaDAO::buscarLocalDeRealizacao($idPreProjeto);
        $this->view->itensDeslocamento = Proposta_Model_AnalisarPropostaDAO::buscarDeslocamento($idPreProjeto);
        $this->view->itensPlanoDivulgacao = Proposta_Model_AnalisarPropostaDAO::buscarPlanoDeDivulgacao($idPreProjeto);

        //DOCUMENTOS ANEXADOS PROPOSTA
        $tbl = new Proposta_Model_DbTable_TbDocumentosPreProjeto();
        $rs = $tbl->buscarDocumentos(array("idProjeto = ?" => $this->idPreProjeto));
        $this->view->arquivosProposta = $rs;

        //DOCUMENTOS ANEXADOS PROPONENTE
        $tbA = new Proposta_Model_DbTable_TbDocumentosAgentes();
        $rsA = $tbA->buscarDocumentos(array("idAgente = ?" => $dados[0]->idAgente));
        $this->view->arquivosProponente = $rsA;

        //DOCUMENTOS ANEXADOS NA DILIGENCIA
        $tblAvaliacaoProposta = new AvaliacaoProposta();
        $rsAvaliacaoProposta = $tblAvaliacaoProposta->buscar(array("idProjeto = ?" => $idPreProjeto, "idArquivo ?" => new Zend_Db_Expr("IS NOT NULL")));
        $tbArquivo = new tbArquivo();
        $arrDadosArquivo = array();
        $arrRelacionamentoAvaliacaoDocumentosExigidos = array();
        if (count($rsAvaliacaoProposta) > 0) {
            foreach ($rsAvaliacaoProposta as $avaliacao) {
                $arrDadosArquivo[$avaliacao->idArquivo] = $tbArquivo->buscar(array("idArquivo = ?" => $avaliacao->idArquivo));
                $arrRelacionamentoAvaliacaoDocumentosExigidos[$avaliacao->idArquivo] = $avaliacao->idCodigoDocumentosExigidos;
            }
        }
        $this->view->relacionamentoAvaliacaoDocumentosExigidos = $arrRelacionamentoAvaliacaoDocumentosExigidos;
        $this->view->itensDocumentoPreProjeto = $arrDadosArquivo;

        //PEGANDO RELACAO DE DOCUMENTOS EXIGIDOS(GERAL, OU SEJA, TODO MUNDO)
        $tblDocumentosExigidos = new DocumentosExigidos();
        $rsDocumentosExigidos = $tblDocumentosExigidos->buscar()->toArray();
        $arrDocumentosExigidos = array();
        foreach ($rsDocumentosExigidos as $documentoExigido) {
            $arrDocumentosExigidos[$documentoExigido["Codigo"]] = $documentoExigido;
        }
        $this->view->documentosExigidos = $arrDocumentosExigidos;
        $this->view->itensHistorico = Proposta_Model_AnalisarPropostaDAO::buscarHistorico($idPreProjeto);
        $this->view->itensPlanilhaOrcamentaria = Proposta_Model_AnalisarPropostaDAO::buscarPlanilhaOrcamentaria($idPreProjeto);

        $buscarProduto = ManterorcamentoDAO::buscarProdutos($this->idPreProjeto);
        $this->view->Produtos = $buscarProduto;

        $tbPlanilhaEtapa = new Proposta_Model_DbTable_TbPlanilhaEtapa();
        $buscarEtapa = $tbPlanilhaEtapa->listarEtapasProdutos($this->idPreProjeto);

        $this->view->Etapa = $buscarEtapa;

        $preProjeto = new Proposta_Model_DbTable_PreProjeto();

        $buscarItem = $preProjeto->listarItensProdutos($this->idPreProjeto);
        $this->view->AnaliseCustos = Proposta_Model_DbTable_PreProjeto::analiseDeCustos($this->idPreProjeto);

        $this->view->idPreProjeto = $this->idPreProjeto;
        $pesquisaView = $this->_getParam('pesquisa');
        if ($pesquisaView == 'proposta') {
            $this->view->menu = 'inativo';
            $this->view->tituloTopo = 'Consultar dados da proposta';
        }

        if ($propostaPorEdital) {
            $tbFormDocumentoDAO = new tbFormDocumento();
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?' => $this->view->itensGeral[0]->idEdital, 'idClassificaDocumento = ?' => $this->COD_CLASSIFICACAO_DOCUMENTO));

            //busca o nome do EDITAL
            $edital = $tbFormDocumentoDAO->buscar(array('idEdital = ?' => $this->view->itensGeral[0]->idEdital));
            $nmEdital = $edital[0]->nmFormDocumento;
            $this->view->nmEdital = $nmEdital;

            $arrPerguntas = array();
            $arrRespostas = array();
            $tbPerguntaDAO = new tbPergunta();
            $tbRespostaDAO = new tbResposta();

            foreach ($edital as $registro) {
                $questoes = $tbPerguntaDAO->montarQuestionario($registro["nrFormDocumento"], $registro["nrVersaoDocumento"]);
                $questionario = '';
                if (is_object($questoes) and count($questoes) > 0) {
                    foreach ($questoes as $questao) {
                        $resposta = '';
                        $where = array(
                            'nrFormDocumento = ?' => $registro["nrFormDocumento"]
                        , 'nrVersaoDocumento = ?' => $registro["nrVersaoDocumento"]
                        , 'nrPergunta = ?' => $questao->nrPergunta
                        , 'idProjeto = ?' => $this->idPreProjeto
                        );
                        $resposta = $tbRespostaDAO->buscar($where);
                        $arrPerguntas[$registro["nrFormDocumento"]]["titulo"] = $registro["nmFormDocumento"];
                        $arrPerguntas[$registro["nrFormDocumento"]]["pergunta"][] = $questao->toArray();
                        $arrRespostas[] = $resposta->toArray();
                    }
                }
            }
            $this->view->perguntas = $arrPerguntas;
            $this->view->respostas = $arrRespostas;

            $this->montaTela("admissibilidade/proposta-por-edital.phtml");
        } else {
            $this->montaTela("admissibilidade/proposta-por-incentivo-fiscal-ajax.phtml");
        }
    }
}
