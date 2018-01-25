<?php
/**
 * Projeto_ConsultardadosprojetoController
 *
 * @package
 * @author ShinNin-Chan <ananji.costa@gmail.com>
 */
class ConsultardadosprojetoControllerTest extends MinC_Test_ControllerActionTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testIndexAction()
    {
        $this->autenticar();
        $this->perfilParaProponente();

        $this->resetRequest()
            ->resetResponse();
	      $idPronac = "501eac548e7d4fa987034573abc6e179MjAxNzEzZUA3NWVmUiEzNDUwb3RT";
        // Acessando local de realizacao
        $url = '/consultardadosprojeto?idPronac=' . $idPronac;
        $this->request->setMethod('GET');
        $this->dispatch($url);
        // $this->assertNotRedirect();

        $this->assertModule('default');
        $this->assertController('consultardadosprojeto');
        $this->assertAction('index');
    }
}
