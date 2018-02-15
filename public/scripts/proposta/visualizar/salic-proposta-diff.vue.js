Vue.component('salic-proposta-diff', {
    template: `
        <div v-if="idpreprojeto" class="proposta">
            <ul class="collapsible" data-collapsible="expandable">
                 <li>
                    <div class="collapsible-header"
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.identificacaoproposta, dadosHistorico.identificacaoproposta)}">
                        <i class="material-icons">assignment</i>Proposta - {{idpreprojeto}} - {{dadosAtuais.NomeProjeto}}
                    </div>
                    <div class="collapsible-body padding20">
                        <div class="row">
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-identificacao :idpreprojeto="idpreprojeto" :proposta="dadosHistorico"></salic-proposta-identificacao>
                            </div>
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-identificacao :idpreprojeto="idpreprojeto" :proposta="dadosAtuais"></salic-proposta-identificacao>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header"><i class="material-icons">person</i>Proponente</div>
                    <div class="collapsible-body padding20">
                        <div class="row">
                            <div class="col s12 m12 12 scroll">
                                <salic-agente-proponente :idagente="dadosAtuais.idAgente"></salic-agente-proponente>
                                <salic-agente-usuario :idusuario="dadosAtuais.idUsuario"></salic-agente-usuario>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.FichaTecnica, dadosHistorico.FichaTecnica)}" i>
                        <i class="material-icons">subject</i>Ficha t&eacute;cnica
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.FichaTecnica"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.FichaTecnica"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.ResumoDoProjeto, dadosHistorico.ResumoDoProjeto)}">
                        <i class="material-icons">subject</i>Resumo
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.ResumoDoProjeto"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.ResumoDoProjeto"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.Objetivos, dadosHistorico.Objetivos)}">
                        <i class="material-icons">subject</i>Objetivos
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.Objetivos"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.Objetivos"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.EtapaDeTrabalho, dadosHistorico.EtapaDeTrabalho)}">
                        <i class="material-icons">subject</i>Etapa de Trabalho
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.EtapaDeTrabalho"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.EtapaDeTrabalho"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.Acessibilidade, dadosHistorico.Acessibilidade)}">
                        <i class="material-icons">subject</i>Acessibilidade
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.Acessibilidade"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.Acessibilidade"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.EspecificacaoTecnica, dadosHistorico.EspecificacaoTecnica)}">
                        <i class="material-icons">subject</i>Especifica&ccedil;&otilde;es t&eacute;cnicas do produto
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.EspecificacaoTecnica"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.EspecificacaoTecnica"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.Sinopse, dadosHistorico.Sinopse)}">
                        <i class="material-icons">subject</i>Sinopse de Obra
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.Sinopse"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.Sinopse"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.DemocratizacaoDeAcesso, dadosHistorico.DemocratizacaoDeAcesso)}">
                        <i class="material-icons">subject</i>Democratiza&ccedil;&atilde;o de Acesso
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                         <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.DemocratizacaoDeAcesso"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.DemocratizacaoDeAcesso"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.Justificativa, dadosHistorico.Justificativa)}">
                        <i class="material-icons">subject</i>Justificativa
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.Justificativa"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.Justificativa"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                 <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.DescricaoAtividade, dadosHistorico.DescricaoAtividade)}">
                        <i class="material-icons">subject</i>Descri&ccedil;&atilde;o de Atividades
                    </div>
                    <div class="collapsible-body padding20" v-if="dadosAtuais">
                        <div class="card">
                            <table>
                                <tr>
                                    <td class="original padding20" v-html="dadosHistorico.DescricaoAtividade"></td>
                                    <td class="changed padding20" v-html="dadosAtuais.DescricaoAtividade"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.abrangencia, dadosHistorico.abrangencia)}">
                        <i class="material-icons">place</i>Local de realiza&ccedil;&atilde;o/Deslocamento
                    </div>
                    <div class="collapsible-body padding20">
                        <div class="row">
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-local-realizacao-deslocamento
                                :localizacoes="dadosAtuais"></salic-proposta-local-realizacao-deslocamento>
                            </div>
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-local-realizacao-deslocamento
                                :localizacoes="dadosHistorico"></salic-proposta-local-realizacao-deslocamento>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="collapsible-header" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.documentos_proposta, dadosHistorico.documentos_proposta)}">
                        <i class="material-icons">attachment</i>Documentos anexados
                    </div>
                    <div class="collapsible-body padding20">
                        <div class="row">
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-documentos :arrayDocumentos="dadosHistorico"></salic-proposta-documentos>
                            </div>
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-documentos :arrayDocumentos="dadosAtuais"></salic-proposta-documentos>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div id="plano-distribuicao" class="collapsible-header active" 
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.tbdetalhaplanodistribuicao, dadosHistorico.tbdetalhaplanodistribuicao)}">
                        <i class="material-icons">equalizer</i>Plano Distribui&ccedil;&atilde;o
                    </div>
                    <div class="collapsible-body padding20 active">
                        <div class="row">
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-plano-distribuicao
                                    :arrayProdutos="dadosAtuais.planodistribuicaoproduto"
                                    :arrayDetalhamentos="dadosAtuais.tbdetalhaplanodistribuicao"
                                ></salic-proposta-plano-distribuicao>
                            </div>
                             <div class="col s12 m6 l6 scroll">
                                <salic-proposta-plano-distribuicao
                                    :arrayProdutos="dadosHistorico.planodistribuicaoproduto"
                                    :arrayDetalhamentos="dadosHistorico.tbdetalhaplanodistribuicao"
                                ></salic-proposta-plano-distribuicao>
                            </div>
                        </div>
                    </div>
                </li>
               <li>
                    <div id="planilha-orcamentaria" class="collapsible-header"
                        v-bind:class="{'orange lighten-4': existe_diferenca(dadosAtuais.tbplanilhaproposta, dadosHistorico.tbplanilhaproposta)}">
                        <i class="material-icons">attach_money</i>Planilha
                        or&ccedil;ament&aacute;ria
                    </div>
                    <div class="collapsible-body padding20 active">
                        <div class="row">
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-planilha-orcamentaria
                                :arrayPlanilha="dadosHistorico.tbplanilhaproposta"></salic-proposta-planilha-orcamentaria>
                            </div>
                            <div class="col s12 m6 l6 scroll">
                                <salic-proposta-planilha-orcamentaria
                                :arrayPlanilha="dadosAtuais.tbplanilhaproposta"></salic-proposta-planilha-orcamentaria>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div v-else class="center-align">
            <div class="padding10 green white-text">Opa! Proposta n�o informada...</div>
        </div>
    `,
    data: function () {
        return {
            dadosAtuais: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            dadosHistorico: {
                type: Object,
                default: function () {
                    return {}
                }
            }
        }
    },
    props: ['idpreprojeto', 'tipo'],
    mounted: function () {
        this.buscar_dados();
    },
    methods: {
        buscar_dados: function () {
            let vue = this;
            $3.ajax({
                url: '/proposta/visualizar/obter-proposta-cultural-versionamento/idPreProjeto/' + vue.idpreprojeto + '/tipo/' + vue.tipo
            }).done(function (response) {
                vue.dadosAtuais = response.data.atual;
                vue.dadosHistorico= response.data.historico;

                if(response.data.historico != 'undefined') {
                    setTimeout(vue.mostrar_diferenca, 1000)
                }
            });
        },
        existe_diferenca: function (atual, historico) {

            if(typeof atual == 'object') {
                return JSON.stringify(atual) != JSON.stringify(historico);
            }

            return atual != historico;
        },
        mostrar_diferenca: function () {
            $(".proposta table tr").prettyTextDiff({
                cleanup: true,
                diffContainer: ".diff",
                debug: false
            });
        }
    }
});

