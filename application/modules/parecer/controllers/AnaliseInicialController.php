<?php

class Parecer_AnaliseInicialController extends MinC_Controller_Action_Abstract implements MinC_Assinatura_Controller_IDocumentoAssinaturaController
{
    private $idPronac;
    private $idProduto;
    private $stPrincipal = 0;
    private $idUsuario = 0;
    private $isIN2017 = false;
    private $somenteLeitura = true;
    private $projeto;
    private $isProjetoDisponivelParaAssinatura;

    private function validarPerfis()
    {
        $auth = Zend_Auth::getInstance();

        $PermissoesGrupo = array();
        $PermissoesGrupo[] = Autenticacao_Model_Grupos::PARECERISTA;

        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);

    }

    public function init()
    {
        parent::perfil();
        parent::init();

        $this->auth = Zend_Auth::getInstance();
        $this->idUsuario = $this->auth->getIdentity()->usu_codigo;

        $this->grupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $this->view->codGrupo = $this->grupoAtivo->codGrupo;

        $this->idPronac = $this->_request->getParam("idPronac");
        $this->idProduto = $this->_request->getParam("idProduto");
        $this->stPrincipal = $this->_request->getParam("stPrincipal");
        $idDistribuirParecer = $this->_request->getParam("idD");

        if ($this->idPronac) {

            $tbProjetos = new Projetos();
            $this->isIN2017 = $tbProjetos->verificarIN2017($this->idPronac);

            if ($this->idProduto) {
                $whereProjeto['p.IdPRONAC = ?'] = $this->idPronac;
                $whereProjeto['d.idProduto = ?'] = $this->idProduto;

                if($idDistribuirParecer) {
                    $whereProjeto['idDistribuirParecer = ?'] = $idDistribuirParecer;
                }

                if($this->stPrincipal) {
                    $whereProjeto['d.stPrincipal = ?'] = $this->stPrincipal;
                }

                $this->projeto = $tbProjetos->buscaProjetosProdutosAnaliseInicial($whereProjeto)->current();
                $pareceristaAtivo = ($this->view->idAgente == $this->projeto['idAgenteParecerista']) ? true : false;

                if (($this->grupoAtivo->codGrupo == Autenticacao_Model_Grupos::PARECERISTA) && $pareceristaAtivo) {

                    $objModelDocumentoAssinatura = new Assinatura_Model_DbTable_TbDocumentoAssinatura();
                    $this->isProjetoDisponivelParaAssinatura = $objModelDocumentoAssinatura->isProjetoDisponivelParaAssinatura(
                        $this->idPronac,
                        Assinatura_Model_DbTable_TbAssinatura::TIPO_ATO_ANALISE_INICIAL
                    );

                    $this->somenteLeitura = false;
//                    if ($this->isProjetoDisponivelParaAssinatura) {
//                        $this->somenteLeitura = true;
//                    }
                }
            }

            $this->view->somenteLeitura = $this->somenteLeitura;
        }
    }

    public function gerenciarAssinaturasAction()
    {
        $this->redirect("/{$this->moduleName}/index");
    }

    public function encaminharAssinaturaAction()
    {
        try {
            $get = $this->getRequest()->getParams();
            $post = $this->getRequest()->getPost();
            $servicoDocumentoAssinatura = $this->obterServicoDocumentoAssinatura();

            if (isset($get['IdPRONAC']) && !empty($get['IdPRONAC']) && $get['encaminhar'] == 'true') {
                $servicoDocumentoAssinatura->idPronac = $get['IdPRONAC'];
                $servicoDocumentoAssinatura->encaminharProjetoParaAssinatura();

                $idTipoDoAtoAdministrativo = Assinatura_Model_DbTable_TbAssinatura::TIPO_ATO_ANALISE_INICIAL;
                $idDocumentoAssinatura = $this->getIdDocumentoAssinatura($get['IdPRONAC'], $idTipoDoAtoAdministrativo);

                $this->redirect("/assinatura/index/visualizar-projeto/?idDocumentoAssinatura=" . $idDocumentoAssinatura . "&origin=" . $get['origin']);
            } elseif (isset($post['IdPRONAC']) && is_array($post['IdPRONAC']) && count($post['IdPRONAC']) > 0) {
                // ainda nao implementado o encaminhamento de vários para pareceres
            }
        } catch (Exception $objException) {
            parent::message($objException->getMessage(), "/{$this->moduleName}/analise-inicial/index");
        }

    }

    function obterServicoDocumentoAssinatura()
    {
        if (!isset($this->servicoDocumentoAssinatura)) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . "AnaliseInicialDocumentoAssinaturaController.php";
            $this->servicoDocumentoAssinatura = new Parecer_AnaliseInicialDocumentoAssinaturaController($this->getRequest()->getPost());
        }
        return $this->servicoDocumentoAssinatura;
    }

    private function getIdDocumentoAssinatura($idPronac, $idTipoDoAtoAdministrativo)
    {
        $objDocumentoAssinatura = new Assinatura_Model_DbTable_TbDocumentoAssinatura();

        $where = array();
        $where['IdPRONAC = ?'] = $idPronac;
        $where['idTipoDoAtoAdministrativo = ?'] = $idTipoDoAtoAdministrativo;
        $where['stEstado = ?'] = 1;

        $result = $objDocumentoAssinatura->buscar($where);

        return $result[0]['idDocumentoAssinatura'];
    }

    public function indexAction()
    {
        $this->validarPerfis();
    }

    public function listarProdutosPorProjetoAjaxAction()
    {
        $start = $this->getRequest()->getParam('start');
        $length = $this->getRequest()->getParam('length');
        $draw = (int)$this->getRequest()->getParam('draw');
        $search = $this->getRequest()->getParam('search');
        $order = $this->getRequest()->getParam('order');
        $columns = $this->getRequest()->getParam('columns');
        $order = ($order[0]['dir'] != 1) ? array($columns[$order[0]['column']]['name'] . ' ' . $order[0]['dir']) : array("IdPRONAC DESC");

        $this->validarPerfis();

        $idAgenteParecerista = $this->view->idAgente;

        if (empty($idAgenteParecerista)) {
            $this->_helper->json(array(
                "data" => 0,
                'recordsTotal' => 0,
                'draw' => 0,
                'recordsFiltered' => 0));
            die;
        }

        $where = array(
            'idAgenteParecerista = ?' => $idAgenteParecerista,
            'idOrgao = ?' => $this->grupoAtivo->codOrgao
        );

        $vwParecerista = new Parecer_Model_DbTable_vwPainelParecerista();

        $projetos = $vwParecerista->listar($where, $order, $start, $length, $search);
        $recordsTotal = 0;
        $recordsFiltered = 0;
        $aux = array();
        if (!empty($projetos)) {
            foreach ($projetos as $key => $projeto) {
                $projeto->NomeProjeto = utf8_encode($projeto->NomeProjeto);
                $projeto->dsProduto = utf8_encode($projeto->dsProduto);
                $projeto->DtDistribuicao = Data::tratarDataZend($projeto->DtDistribuicao, 'Brasileira');

                $aux[$key] = $projeto;
            }

            $recordsFiltered = $vwParecerista->listarTotal($where, $order, $start, $length, $search);
            $recordsTotal = $vwParecerista->listarTotal($where);
        }

        $this->_helper->json(array(
                "data" => !empty($aux) ? $aux : 0,
                'recordsTotal' => $recordsTotal ? $recordsTotal : 0,
                'draw' => $draw,
                'recordsFiltered' => $recordsFiltered ? $recordsFiltered : 0)
        );
    }

    public function analisarAction()
    {
        try {

            if (!$this->projeto) {
                throw new Exception ("Projeto n&atilde;o encontrado!");
            }

            $this->view->projeto = $this->projeto;
            $this->view->IN2017 = $this->isIN2017;
            $this->view->situacao = self::definirSituacaoAnaliseProduto(
                $this->projeto->DtDistribuicao,
                $this->projeto->DtDevolucao,
                $this->projeto->FecharAnalise,
                $this->projeto->idAgenteParecerista
            );

            if ($this->projeto->stPrincipal) {

                $produtosSecundarios = self::obterProdutosSecundarios($this->idPronac);
                $this->view->existeProdutoSecundario = count($produtosSecundarios) > 0 ? true : false;
                $this->view->produtosSecundarios = $produtosSecundarios;

                self::obterConsolidacaoParecerTecnico();
            }

        } catch (Exception $e) {
            parent::message($e->getMessage(), "parecer/analise-inicial", "ERROR");
        }
    }

    public function obterAnaliseConteudoAction()
    {
        $this->_helper->layout->disableLayout();

        $analisedeConteudoDAO = new Analisedeconteudo();
        $dadosAnaliseConteudo = $analisedeConteudoDAO->dadosAnaliseconteudo($this->idPronac, ['idProduto = ?' => $this->idProduto])
            ->current()
            ->toArray();

        foreach ($dadosAnaliseConteudo as $key => $val) {
            $dadosAnaliseConteudo[$key] = utf8_encode($val);
        }

        if ($this->isIN2017) {
            $tbAcaoAlcanteProjeto = new tbAcaoAlcanceProjeto();
            $buscarAcaoAlcanceProjeto = $tbAcaoAlcanteProjeto->buscar(
                array(
                    'idPronac = ?' => $this->idPronac,
                    'idParecer = ?' => $dadosAnaliseConteudo['idAnaliseDeConteudo']
                )
            );

            if (count($buscarAcaoAlcanceProjeto) > 0) {
                foreach ($buscarAcaoAlcanceProjeto->current() as $key => $val) {
                    $dadosAnaliseConteudo[$key] = utf8_encode($val);
                }
            }
        }

        $this->_helper->json($dadosAnaliseConteudo);
    }

    /*@todo revisar codigo*/
    public function produtosAnaliseAjaxAction()
    {
        $this->validarPerfis();

        $idPRONAC = $this->_request->getParam('idPRONAC');

        $auth = Zend_Auth::getInstance();
        $idusuario = $auth->getIdentity()->usu_codigo;
        $this->view->idUsuario = $idusuario;

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $idOrgao = $GrupoAtivo->codOrgao;

        $UsuarioDAO = new Autenticacao_Model_Usuario();
        $agente = $UsuarioDAO->getIdUsuario($idusuario);
        $idAgenteParecerista = $agente['idagente'];
        $this->view->idAgenteParecerista = $idAgenteParecerista;

        $situacao = $this->_request->getParam('situacao');

        $projeto = new Projetos();
        $resp = $projeto->projetosParaAnaliseProdutos(
            array(
                'distribuirParecer.idAgenteParecerista = ?' => $idAgenteParecerista,
                'distribuirParecer.idOrgao = ?' => $idOrgao,
                'projeto.idPRONAC = ?' => $idPRONAC
            )
        );

        $produtoAux = [];
        foreach ($resp->toArray() as $key => $produto) {
            $produtoAux[$key] = $produto;

            $produtoAux[$key]['dsProduto'] = utf8_encode($produtoAux[$key]['dsProduto']);
        }

        $this->_helper->json($produtoAux);
    }

    private function obterProdutosSecundarios($idPronac)
    {
        $produtosSecundarios = [];

        $dadosWhere["t.stEstado = ?"] = 0;
        $dadosWhere["t.TipoAnalise in (?)"] = array(1, 3);
        $dadosWhere["p.Situacao IN ('B11', 'B14')"] = '';
        $dadosWhere["p.IdPRONAC = ?"] = $idPronac;
        $dadosWhere["t.stPrincipal = ?"] = 0;

        $tbDistribuirParecer = new tbDistribuirParecer();
        $Secundarios = $tbDistribuirParecer->dadosParaDistribuirSecundarios($dadosWhere);

        $dadosWhere["t.DtDistribuicao is not null"] = '';
        $dadosWhere["t.DtDevolucao is null"] = '';
        $SecundariosAtivos = $tbDistribuirParecer->dadosParaDistribuir($dadosWhere);
        $pscount = count($SecundariosAtivos);

        $i = 1;
        foreach ($Secundarios as $ps) {

            $produtosSecundarios[$i]['IdPRONAC'] = $ps->IdPRONAC;
            $produtosSecundarios[$i]['idProduto'] = $ps->idProduto;
            $produtosSecundarios[$i]['stPrincipal'] = $ps->stPrincipal;
            $produtosSecundarios[$i]['Produto'] = $ps->Produto;
            $produtosSecundarios[$i]['DescricaoAnalise'] = $ps->DescricaoAnalise;
            $produtosSecundarios[$i]['Obs'] = $ps->Obs;
            $produtosSecundarios[$i]['Situacao'] = self::definirSituacaoAnaliseProduto(
                $ps->DtDistribuicaoPT,
                $ps->DtDevolucaoPT,
                $ps->FecharAnalise,
                $ps->idAgenteParecerista
            );


            $i++;
        }

        return $produtosSecundarios;
    }

    private function definirSituacaoAnaliseProduto($dtDistribuicao, $dtDevolucao, $fecharAnalise, $idAgenteParecerista)
    {

        if (!empty($dtDistribuicao)) {
            $situacao = 'Aguardando an&aacute;lise de outro parecerista';

            if ($idAgenteParecerista == $this->view->idAgente) {
                $situacao = 'Aguardando sua an&aacute;lise';
            }

            if (!empty($dtDevolucao)) {
                $situacao = 'An&aacute;lise concluida e n&atilde;o finalizada';

                if ($fecharAnalise == 1) {
                    $situacao = 'An&aacute;lise finalizada';
                }
            }

        } else {
            $situacao = 'Aguardando distribui&ccedil;&atilde;o';

            if (!empty($dtDevolucao) && $fecharAnalise == 0) {
                $situacao = 'Devolvida p/ an&aacute;lise';
            }
        }

        return $situacao;
    }

    public function salvarAnaliseConteudoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $auth = Zend_Auth::getInstance(); // pega a autenticacao
        $idusuario = $auth->getIdentity()->usu_codigo;

        $dsJustificativa = $this->_request->getParam("ParecerDeConteudo");
        $idPronac = $this->_request->getParam("idPronac");
        $idProduto = $this->_request->getParam("idProduto");
        $stPrincipal = $this->_request->getParam("stPrincipal");
        $idD = $this->_request->getParam("idD");

        try {

            if ($this->somenteLeitura === true) {
                throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o salvar essa an&aacute;lise de conte&uacute;do");
            }

            if (!$this->_request->getParam('ParecerFavoravel')) {
                $planilhaProjeto = new PlanilhaProjeto();
                $atualizar = array(
                    'idUnidade' => 1,
                    'Quantidade' => 0,
                    'Ocorrencia' => 0,
                    'ValorUnitario' => 0,
                    'QtdeDias' => 0,
                    'idUsuario' => $idusuario,
                    'Justificativa' => ''
                );

                if ($stPrincipal) {
                    $planilhaProjeto->alterar($atualizar, array('idPRONAC = ?' => $idPronac));
                } else {
                    $planilhaProjeto->alterar($atualizar, array('idPRONAC = ?' => $idPronac, 'idProduto = ?' => $idProduto));
                }
            } else {
                $analisedeConteudoDAO = new Analisedeconteudo();
                $whereB['idPronac  = ?'] = $idPronac;
                $whereB['idProduto = ?'] = $idProduto;
                $buscaAnaliseConteudo = $analisedeConteudoDAO->buscar($whereB);

                if ($buscaAnaliseConteudo[0]->ParecerFavoravel == 0) {
                    $copiaPlanilha = PlanilhaPropostaDAO::parecerFavoravel($idPronac, $idProduto);
                }
            }

            $dados = array(
                'Lei8313' => $this->_request->getParam('Lei8313'),
                'Artigo3' => $this->_request->getParam('Artigo3'),
                'IncisoArtigo3' => $this->_request->getParam('IncisoArtigo3'),
                'AlineaArtigo3' => $this->_request->getParam('AlineaArtigo3'),
                'Artigo18' => $this->_request->getParam('Artigo18'),
                'AlineaArtigo18' => $this->_request->getParam('AlineaArtigo18'),
                'Artigo26' => $this->_request->getParam('Artigo26'),
                'Lei5761' => $this->_request->getParam('Lei5761'),
                'Artigo27' => $this->_request->getParam('Artigo27'),
                'IncisoArtigo27_I' => $this->_request->getParam('IncisoArtigo27_I'),
                'IncisoArtigo27_II' => $this->_request->getParam('IncisoArtigo27_II'),
                'IncisoArtigo27_III' => $this->_request->getParam('IncisoArtigo27_III'),
                'IncisoArtigo27_IV' => $this->_request->getParam('IncisoArtigo27_IV'),
                'TipoParecer' => 1,
                'ParecerFavoravel' => $this->_request->getParam('ParecerFavoravel'),
                'ParecerDeConteudo' => $dsJustificativa,
                'idUsuario' => $idusuario
            );

            $analisedeConteudoDAO = new Analisedeconteudo();
            $where['idPRONAC = ?'] = $idPronac;
            $where['idProduto = ?'] = $idProduto;
            $analisedeConteudoDAO->update($dados, $where);

            $this->_helper->json(array('status' => true, 'msg' => "Altera&ccedil;&atilde;o realizada com sucesso!"));
        } catch (Exception $e) {
            $this->_helper->json(array('status' => false, 'msg' => $e->getMessage()));
        }
    }

    public function analisarCustosAction()
    {
        $this->_helper->layout->disableLayout();

        $idPronac = $this->_request->getParam("idPronac");
        $idProduto = $this->_request->getParam("idProduto");
        $stPrincipal = $this->_request->getParam("stPrincipal");

        $this->itens = self::obterItensOrcamentariosPorProduto($idPronac, $idProduto, $stPrincipal);
        $this->view->itens = $this->itens;
    }

    public function obterItensOrcamentariosPorProduto($idPronac, $idProduto, $stPrincipal = false)
    {
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $codGrupo = $GrupoAtivo->codGrupo;

        $analisedeConteudoDAO = new Analisedeconteudo();
        $analisedeConteudo = $analisedeConteudoDAO->dadosAnaliseconteudo(false, array('idPronac = ?' => $idPronac, 'idProduto = ?' => $idProduto));

        $PlanilhaDAO = new PlanilhaProjeto();
        if ($stPrincipal == 1) {
            $where = array('PPJ.IdPRONAC = ?' => $idPronac, 'PPJ.IdProduto in (0, ?)' => $idProduto);
        } else {
            $where = array('PPJ.IdPRONAC = ?' => $idPronac, 'PPJ.IdProduto = ?' => $idProduto, 'PD.Descricao is not null' => null);
        }

        $resp = $PlanilhaDAO->buscarAnaliseCustos($where);
        $itensCusto = array('fonte' => array(), 'totalSolicitado' => 0, 'totalSugerido' => 0);
        $cont = true;

        foreach ($resp as $key => $val) {
            $produto = $val->Produto == null ? 'Administra&ccedil;&atilde;o do Projeto' : $val->Produto;
            if (!isset($itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['qtd'])) {
                $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade] = array('qtd' => 0, 'totalUfSolicitado' => 0, 'totalUfSugerido' => 0, 'itens' => array(), 'totalSolicitado' => 0, 'totalSugerido' => 0);
            }
            $itensCusto['totalSolicitado'] += $val->VlSolicitado;
            $itensCusto['totalSugerido'] += $val->VlSugeridoParecerista;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['qtd']++;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['totalUfSolicitado'] += $val->VlSolicitado;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['totalUfSugerido'] += $val->VlSugeridoParecerista;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['&nbsp;'] = $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['qtd'];

            // So pode alterar se for incentivo fiscal - FonteRecurso = 109
            if (($analisedeConteudo[0]->ParecerFavoravel == 1) && ($val->idEtapa != 4)) {
                if ($codGrupo == Autenticacao_Model_Grupos::PARECERISTA) {
                    $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Item'] = "<a href='javascript:void(0);' onclick='javascript:AlterarItem({$val->idPlanilhaProjeto},{$idPronac},{$idProduto},{$stPrincipal})'>{$val->Item}</a>";
                } else if ($codGrupo == Autenticacao_Model_Grupos::COORDENADOR_DE_PARECERISTA) {
                    $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Item'] = $val->Item;
                }
            } else if (($analisedeConteudo[0]->ParecerFavoravel == 1) && ($stPrincipal == 1)) {
                if ($codGrupo == Autenticacao_Model_Grupos::PARECERISTA) {
                    $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Item'] = "<a href='javascript:void(0);' onclick='javascript:AlterarItem({$val->idPlanilhaProjeto},{$idPronac},{$idProduto},{$stPrincipal})'>{$val->Item}</a>";
                } else if ($codGrupo == Autenticacao_Model_Grupos::COORDENADOR_DE_PARECERISTA) {
                    $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Item'] = $val->Item;
                }

            } else {
                $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Item'] = "{$val->Item}";
            }

            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Dias'] = $val->diasprop;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Unidade'] = $val->UnidadeProposta;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Quantidade'] = number_format($val->quantidadeprop, 0, '.', ',');
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Ocorr&ecirc;ncias'] = number_format($val->ocorrenciaprop, 0, '.', ',');
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Valor Unit&aacute;rio'] = $val->valorUnitarioprop;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Valor Solicitado'] = $val->VlSolicitado;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Justificativa do Proponente'] = $val->justificitivaproponente;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Valor Sugerido pelo Parecerista'] = $val->VlSugeridoParecerista;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Justificativas do Parecerista'] = $val->dsJustificativaParecerista;
            $itensCusto['fonte'][$val->FonteRecurso][$produto][$val->idEtapa . ' - ' . $val->Etapa][$val->UF . ' - ' . $val->Cidade]['itens'][$val->idPlanilhaProjeto]['Custo praticado'] = $val->custopraticado;
        }

        foreach ($itensCusto['fonte'] as $key => $value) {
            foreach ($value as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {
                    foreach ($value3 as $key4 => $value4) {

                        if ($itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSolicitado'] != 0) {
                            $itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSolicitado'] = number_format($itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSolicitado'], 2, ',', '.');
                        } else {
                            $itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSolicitado'] = "0,00";
                        }

                        if ($itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSugerido'] != 0) {
                            $itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSugerido'] = number_format($itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSugerido'], 2, ',', '.');
                        } else {
                            $itensCusto['fonte'][$key][$key2][$key3][$key4]['totalUfSugerido'] = "0,00";
                        }
                    }
                }
            }
        }

        $valorPossivel = $itensCusto['totalSolicitado'] - $itensCusto['totalSugerido'];
        $valorSolicitado = $itensCusto['totalSolicitado'];
        if ($itensCusto['totalSolicitado'] != 0) {
            $itensCusto['totalSolicitado'] = number_format($itensCusto['totalSolicitado'], 2, ',', '.');
        } else {
            $itensCusto['totalSugerido'] = "0,00";
        }

        if ($itensCusto['totalSugerido'] != 0) {
            $itensCusto['totalSugerido'] = number_format($itensCusto['totalSugerido'], 2, ',', '.');
        } else {
            $itensCusto['totalSugerido'] = "0,00";
        }

        $itensCusto['valorPossivel'] = $valorPossivel;
        $itensCusto['vlSolicitado'] = $valorSolicitado;

        return $itensCusto;
    }

    public function sugerirItemCustoModalAction()
    {
        $this->_helper->layout->disableLayout();
        $idPronac = $this->_request->getParam("idPronac");
        $idProduto = $this->_request->getParam("idProduto");
        $stPrincipal = $this->_request->getParam("stPrincipal");
        $idPlanilhaProjeto = $this->_request->getParam("idPlanilhaProjeto");

        $this->view->planilhaUnidade = PlanilhaUnidadeDAO::buscar();

        $projetoDAO = new Projetos();
        $projeto = $projetoDAO->buscaProjetosProdutosAnaliseInicial(
            array(
                'p.IdPRONAC = ?' => $idPronac,
                'd.idProduto = ?' => $idProduto,
                'd.stPrincipal = ?' => $stPrincipal
            )
        );
        $this->view->projeto = $projeto[0];

        $PlanilhaDAO = new PlanilhaProjeto();
        $planilha = $PlanilhaDAO->buscarAnaliseCustos(array('PPJ.idPlanilhaProjeto= ?' => $idPlanilhaProjeto));
        $this->view->dadosItem = $planilha[0];
    }

    public function salvarSugestaoItemCustoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        try {

            if($this->somenteLeitura) {
                throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o de sugerir item para este projeto");
            }

            $params = $this->_request->getParams();

            $dados["idUnidade"] = $params["idUnidade"];
            $dados["Quantidade"] = $params["Quantidade"];
            $dados["Ocorrencia"] = $params["Ocorrencia"];
            $dados['ValorUnitario'] = str_replace('.', '',$params['ValorUnitario']);
            $dados['ValorUnitario'] = str_replace(',', '.', $dados['ValorUnitario']);
            $dados["QtdeDias"] = $params["QtdeDias"];
            $dados["Justificativa"] = utf8_decode($params['Justificativa']);
            $dados["idUsuario"] = $this->idUsuario;

            $where = array('idPlanilhaProjeto = ?' => $params['idPlanilhaProjeto']);
            $planilhaProjeto = new PlanilhaProjeto();
            $planilhaProjeto->alterar($dados, $where);

            $this->_helper->json(array('status' => true, 'msg' => "Item sugerido salvo com sucesso!"));
        } catch (Exception $e) {
            $this->_helper->json(array('status' => false, 'msg' => $e->getMessage()));
        }

    }

    private function obterConsolidacaoParecerTecnico() {

        $mapperArea = new Agente_Model_AreaMapper();
        $this->view->comboareasculturais = $mapperArea->fetchPairs('Codigo', 'Descricao');

        $objSegmentocultural = new Segmentocultural();
        $this->view->combosegmentosculturais = $objSegmentocultural->buscarSegmento($this->projeto->Area);

        $whereParecer['idPRONAC = ?'] = $this->idPronac;
        $parecerDAO = new Parecer();
        $this->view->consolidacao = $parecerDAO->buscar($whereParecer);

        $tbAcaoAlcanceProjeto = new tbAcaoAlcanceProjeto();
        $buscarAcaoAlcanceProjeto = $tbAcaoAlcanceProjeto->buscar(array('idPronac = ?' => $this->idPronac));
        if (count($buscarAcaoAlcanceProjeto) > 0) {
            $this->view->alcance = $buscarAcaoAlcanceProjeto[0];
        }

        $tbProjetos = new Projetos();
        $this->view->dadosEnquadramento = $tbProjetos->enquadramentoProjeto($this->idPronac);
    }

    public function salvarConsolidacaoParecerTecnicoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $auth = Zend_Auth::getInstance();
        $idusuario = $auth->getIdentity()->usu_codigo;

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $idOrgao = $GrupoAtivo->codOrgao; //  orgao ativo na sessao

        $params = $this->_request->getParams();
        $idProduto = $params['idProduto'];
        $stPrincipal = $params['stPrincipal'];
        $areaCultural = $params['areaCultural'];
        $segmentoCultural = $params['segmentoCultural'];
        $enquadramentoProjeto = $params['enquadramentoProjeto'];
        $dsConsolidacao = $params["dsConsolidacao"];
        $idPronac = $params['idPronac'];
        $anoProjeto = $params['AnoProjeto'];
        $sequencial = $params['Sequencial'];
        $parecerProjeto = $params['parecerProjeto'];
        $sugeridoReal = Mascara::delMaskMoeda($params['sugeridoReal']);
        $sugeridoReal = str_replace("R$", "", $sugeridoReal);

        try {

            if(empty($idPronac)) {
                throw new Exception("Projeto n&atilde;o encontrado!");
            }

            if($this->somenteLeitura) {
                throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o para consolidar este projeto");
            }

            $planilha = self::obterItensOrcamentariosPorProduto($idPronac, $idProduto, $stPrincipal);

            if($planilha) {
                $sugeridoReal =  Mascara::delMaskMoeda($planilha['totalSugerido']);
            }

            if (!$this->isIN2017) {

                $pa = new paChecarLimitesOrcamentario();
                $resultadoCheckList = $pa->exec($idPronac, 2);

                $i = 0;
                $erroLimiteOrcamentario = false;
                $mensagemErro = '';
                foreach ($resultadoCheckList as $resultado) {
                    if ($resultado->Observacao == 'PENDENTE') {
                        $i++;
                        $mensagemErro .= utf8_encode($resultado->Descricao) . ' ' . $resultado->vlDiferenca . ' ' . '<br>';
                    }
                }

                if ($i > 0) {
                    $this->view->resultadoCheckList = $resultadoCheckList;
                    $erroLimiteOrcamentario = true;
                }

                if ($erroLimiteOrcamentario) {
                    throw new Exception($mensagemErro);
                }
            }

            /** Fazendo um Update no Projeto enquadrando na area e Segmento ******************************/
            if ($areaCultural <> 0) {
                $projetoDAO = new Projetos();
                $dadosProjeto = array('Area' => $areaCultural, 'Segmento' => $segmentoCultural, 'Logon' => $idusuario);
                $where['IdPRONAC = ?'] = $idPronac;
                $alteraProjeto = $projetoDAO->update($dadosProjeto, $where);
            }

            $enquadramentoDAO = new Admissibilidade_Model_Enquadramento();
            if (!$this->isIN2017) {
                /** Gravando as informacoes do enquadramento do Projeto ***************************************/
                $dadosEnquadramento = array(
                    'IdPRONAC' => $idPronac,
                    'AnoProjeto' => $anoProjeto,
                    'Sequencial' => $sequencial,
                    'Enquadramento' => $enquadramentoProjeto,
                    'DtEnquadramento' => MinC_Db_Expr::date(),
                    'Observacao' => '',
                    'Logon' => $idusuario
                );

                $whereBuscarDados = array('IdPRONAC = ?' => $idPronac, 'AnoProjeto = ?' => $anoProjeto, 'Sequencial = ?' => $sequencial);
                $buscarEnquadramento = $enquadramentoDAO->buscar($whereBuscarDados);

                if (count($buscarEnquadramento) > 0) {
                    $buscarEnquadramento = $buscarEnquadramento->current();
                    $whereUpdate = 'idEnquadramento = ' . $buscarEnquadramento->IdEnquadramento;
                    $alteraEnquadramento = $enquadramentoDAO->alterar($dadosEnquadramento, $whereUpdate);
                } else {
                    $insereEnquadramento = $enquadramentoDAO->inserir($dadosEnquadramento);
                }
            }

            $buscaEnquadramento = $enquadramentoDAO->buscarDados($idPronac, null, false);

            $parecerDAO = new Parecer();
            $dadosParecer = array(
                'idPRONAC' => $idPronac,
                'AnoProjeto' => $anoProjeto,
                'Sequencial' => $sequencial,
                'TipoParecer' => 1,
                'ParecerFavoravel' => $parecerProjeto,
                'DtParecer' => MinC_Db_Expr::date(),
                'NumeroReuniao' => null,
                'ResumoParecer' => $dsConsolidacao,
                'SugeridoReal' => $sugeridoReal,
                'Atendimento' => 'S',
                'idEnquadramento' => $buscaEnquadramento['IdEnquadramento'],
                'stAtivo' => 1,
                'idTipoAgente' => 1,
                'Logon' => $idusuario
            );

            $buscarParecer = $parecerDAO->buscar(
                array(
                    'IdPRONAC = ?' => $idPronac,
                    'AnoProjeto = ?' => $anoProjeto,
                    'Sequencial = ?' => $sequencial
                )
            );

            if (count($buscarParecer) > 0) {
                $buscarParecer = $buscarParecer->current();
                $whereUpdateParecer = 'IdParecer = ' . $buscarParecer->IdParecer;
                $alteraParecer = $parecerDAO->alterar($dadosParecer, $whereUpdateParecer);
                $idParecer = $buscarParecer->IdParecer;
            } else {
                $insereParecer = $parecerDAO->inserir($dadosParecer);
                $idParecer = $insereParecer;

            }

            if ($this->isIN2017 && $stPrincipal) {
                $tbAcaoAlcanceProjeto = new tbAcaoAlcanceProjeto();
                $buscarAcaoAlcanceProjeto = $tbAcaoAlcanceProjeto->buscar(array('idPronac = ?' => $idPronac, 'idParecer = ?' => $idParecer));

                $dados = array(
                    'idPronac' => $idPronac,
                    'idParecer' => $idParecer,
                    'tpAnalise' => 1,
                    'dtAnalise' => MinC_Db_Expr::date(),
                    'dsAcaoAlcanceProduto' => $params["dsAcaoAlcanceProduto"],
                    'idUsuario' => $idusuario,
                    'stEstado' => 1
                );

                if (count($buscarAcaoAlcanceProjeto) > 0) {
                    $where = array(
                        'idPronac = ?' => $idPronac,
                        'idParecer = ?' => $idParecer
                    );

                    $tbAcaoAlcanceProjeto->update($dados, $where);
                } else {
                    $tbAcaoAlcanceProjeto->insert($dados);
                }
            }

            $this->_helper->json(array('status' => true, 'msg' => "Consolida&ccedil;&atilde;o salva com sucesso!"));
        } catch (Exception $e) {
            $this->_helper->json(array('status' => false, 'msg' => $e->getMessage()));
        }
    }

    public function obterFinalizacaoParecerModalAction()
    {
        $this->_helper->layout->disableLayout();

        if($this->somenteLeitura && !$this->isProjetoDisponivelParaAssinatura) {
            $this->_helper->viewRenderer->setNoRender(true);
        }

        $idPronac = $this->_request->getParam("idPronac");
        $idProduto = $this->_request->getParam("idProduto");
        $stPrincipal = $this->_request->getParam("stPrincipal");

        $projetoDAO = new Projetos();

        $whereProjeto['p.IdPRONAC = ?'] = $idPronac;
        $whereProjeto['d.idProduto = ?'] = $idProduto;
        $whereProjeto['d.stPrincipal = ?'] = $stPrincipal;

        $this->view->projeto = $projetoDAO->buscaProjetosProdutosAnaliseInicial($whereProjeto)->current();

        $tbDiligencia = new tbDiligencia();
        $this->view->quantidadeDiligencias = count($tbDiligencia->buscarDados($this->idPronac));

        $this->view->codGrupo = $this->grupoAtivo->codGrupo;
        $this->view->idTipoDoAtoAdministrativo = Assinatura_Model_DbTable_TbAssinatura::TIPO_ATO_ANALISE_INICIAL;
        $this->view->idTipoAtoAnaliseInicial = Assinatura_Model_DbTable_TbAssinatura::TIPO_ATO_ANALISE_INICIAL;
        $this->view->IN2017 = $this->isIN2017;
    }

    public function salvarFinalizacaoParecerAction()
    {
        $idDistribuirParecer = $this->_request->getParam("idD");
        $this->view->totaldivulgacao = "true";
        try {

            if ($this->somenteLeitura) {
                throw new Exception("Você não pode finalizar este parecer");
            }

            if ($this->isIN2017) {
                $this->validacaoAnteriorIN2017($this->idPronac);
            }

            if ($this->getRequest()->isPost() || $this->_request->getParam("concluir") == 1) {
                $justificativa = ($this->_request->getParam("concluir") == 1) ? "" : trim(strip_tags($this->_request->getParam("justificativa")));
                $tbDistribuirParecer = new tbDistribuirParecer();
                $dadosWhere["t.idDistribuirParecer = ?"] = $idDistribuirParecer;
                $buscaDadosProjeto = $tbDistribuirParecer->dadosParaDistribuir($dadosWhere);

                try {
                    $tbDistribuirParecer->getAdapter()->beginTransaction();
                    foreach ($buscaDadosProjeto as $dp):

                        $fecharAnalise = 0;

                        $dados = array(
                            'idOrgao' => $dp->idOrgao,
                            'DtEnvio' => $dp->DtEnvio,
                            'idAgenteParecerista' => $dp->idAgenteParecerista,
                            'DtDistribuicao' => $dp->DtDistribuicao,
                            'DtDevolucao' => MinC_Db_Expr::date(),
                            'DtRetorno' => null,
                            'FecharAnalise' => $fecharAnalise,
                            'Observacao' => $justificativa,
                            'idUsuario' => $this->idUsuario,
                            'idPRONAC' => $dp->IdPRONAC,
                            'idProduto' => $dp->idProduto,
                            'TipoAnalise' => $dp->TipoAnalise,
                            'stEstado' => 0,
                            'stPrincipal' => $dp->stPrincipal,
                            'stDiligenciado' => null
                        );

                        $where['idDistribuirParecer = ?'] = $idDistribuirParecer;

                        $salvar = $tbDistribuirParecer->alterar(array('stEstado' => 1), $where);

                        $insere = $tbDistribuirParecer->inserir($dados);

                    endforeach;

                    $tbDistribuirParecer->getAdapter()->commit();

                    parent::message("An&aacute;lise conclu&iacute;da com sucesso !", "parecer/analise-inicial", "CONFIRM");

                } catch (Zend_Db_Exception $e) {
                    $tbDistribuirParecer->getAdapter()->rollBack();
                    parent::message("Error" . $e->getMessage(), "parecer/analise-inicial", "ERROR");
                }
            }

            $projetos = new Projetos();
            $dadosProjetoProduto = $projetos->dadosFechar($this->idUsuario, $this->idPronac, $idDistribuirParecer);
            $this->view->dados = $dadosProjetoProduto;

            $this->view->IN2017 = $this->isIN2017;
            $this->view->idpronac = $this->idPronac;
        } catch (Exception $e) {
            parent::message("Error" . $e->getMessage(), "parecer/analise-inicial", "ERROR");
        }
    }

    private function validacaoAnteriorIN2017($idPronac)
    {
        // Validacao do 20%
        //valor total do projeto V1

        $planilhaProjeto = new PlanilhaProjeto();
        $valorProjeto = $planilhaProjeto->somarPlanilhaProjeto($idPronac, 109);
        //Validacao dos 20%
        if ($valorProjeto['soma'] > 0 && $this->stPrincipal == "1") {
            $this->view->totaldivulgacao = $this->validaRegra20Porcento($idPronac);
        }

        // Validacao do 15%
        if ($this->stPrincipal == "1") //avaliacao da regra dos 15% so deve ser feita quando a analise for do produto principal
        {
            $Situacao = false;

            $V1 = '';
            $V2 = '';
            $V3 = '';
            $V4 = '';
            $V5 = '';
            $V6 = '';

            $tpPlanilha = 'CO'; // O que eh isso?
            $planilhaProjeto = new PlanilhaProjeto();

            /* V1 */

            $whereTotalV1['PAP.IdPRONAC = ?'] = $idPronac;
            $whereTotalV1['PAP.FonteRecurso = ?'] = 109;
            $whereTotalV1['PAP.idPlanilhaItem <> ? '] = 206;

            $valorProjeto15 = $planilhaProjeto->somaDadosPlanilha($whereTotalV1);

            $V1 = $valorProjeto15['soma'];

            /* V2 */
            $whereTotalV2['PAP.IdPRONAC = ?'] = $idPronac;
            $whereTotalV2['PAP.FonteRecurso = ?'] = 109;
            $whereTotalV2['PAP.idEtapa = ? '] = 4;
            $whereTotalV2['PAP.idProduto = ?'] = 0;
            $whereTotalV2['PAP.idPlanilhaItem not in (?)'] = array(5249, 206, 1238);

            $valoracustosadministrativos = $planilhaProjeto->somaDadosPlanilha($whereTotalV2);
            $V2 = $valoracustosadministrativos['soma'];

            /* 15% */
            if ($V1 > 0 and $valoracustosadministrativos['soma'] < $valorProjeto['soma']) {
                //Calcula os 15% do valor total do projeto V3
                $quinzecentoprojeto = $V1 * 0.15;

                //Subtrai os custos administrativos pelos 15% do projeto (V2 - V3)
                $verificacaonegativo = $valoracustosadministrativos['soma'] - $quinzecentoprojeto;
                //V4
                if ($verificacaonegativo < 0) {
                    //x(0);
                    $this->view->verifica15porcento = 0;
                } else {
                    //V1 - V4 = V5
                    /*V5*/
                    $valorretirar = /*V1*/
                        $V1 - /*V4*/
                        $verificacaonegativo;
                    /*V6*/
                    $quinzecentovalorretirar = /*V5*/
                        $valorretirar * 0.15;
                    //V2 - V6
                    $valorretirarplanilha = $valoracustosadministrativos['soma'] - $quinzecentovalorretirar; //(correcao V2 - V6)
                    $this->view->verifica15porcento = $valorretirarplanilha;
                }
            } else {
                $this->view->verifica15porcento = $valoracustosadministrativos['soma'];
            }
        }
    }
}