<?php

class Solicitacao_TestController extends Solicitacao_GenericController {

    public function indexAction () {
        xd("Teste >> M&oacute;dulo " .  ucfirst($this->moduleName));
    }
}
