Vue.component('salic-proposta-detalhamento-plano-distribuicao', {
    template: `
    <div class="detalhamento-plano-distribuicao">
        <ul class="collapsible" data-collapsible="expandable">
            <li v-for="( detalhamento, local ) in detalhamentos">
                <div class="collapsible-header">
                    <i class="material-icons">place</i>
                    Detalhamento - {{local}}
                </div>
                <div class="collapsible-body no-padding margin20 scroll-x">
                    <table>
                        <thead v-if="detalhamento.length > 0">
                        <tr>
                            <th rowspan="2">Categoria</th>
                            <th rowspan="2">Qtd.</th>
                            <th class="center-align gratuito" rowspan="2">
                                Dist. <br>Gratuita
                            </th>
                            <th class="center-align popular" colspan="3">
                                Pre&ccedil;o Popular
                            </th>
                            <th class="center-align proponente" colspan="3">
                                Proponente
                            </th>
                            <th rowspan="2" class="center-align">Receita <br> Prevista</th>
                        </tr>
                        <tr>
                            <th class="right-align popular">Qtd. Inteira</th>
                            <th class="right-align popular">Qtd. Meia</th>
                            <th class="right-align popular">Pre&ccedil;o <br> Unit&aacute;rio</th>
                            <th class="right-align proponente">Qtd. Inteira</th>
                            <th class="right-align proponente">Qtd. Meia</th>
                            <th class="right-align proponente">Pre&ccedil;o <br> Unit&aacute;rio</th>
                        </tr>
                        </thead>
                        <tbody v-if="detalhamento.length > 0">
                            <tr v-for="( item, index ) in detalhamento">
                                <td>{{item.dsProduto}}</td>
                                <td class="right-align">{{ item.qtExemplares }}</td>
            
                                <!-- Distribuicao Gratuita-->
                                <td class="right-align">{{ parseInt(item.qtGratuitaDivulgacao) +
                                    parseInt(item.qtGratuitaPatrocinador) + parseInt(item.qtGratuitaPopulacao) }}
                                </td>
            
                                <!--Preço Popular -->
                                <td class="right-align">{{ item.qtPopularIntegral }}</td>
                                <td class="right-align">{{ item.qtPopularParcial }}</td>
                                <td class="right-align">{{ formatarValor(item.vlUnitarioPopularIntegral) }}</td>
            
                                <!--Preço Proponente -->
                                <td class="right-align">{{ item.qtProponenteIntegral }}</td>
                                <td class="right-align">{{ item.qtProponenteParcial }}</td>
                                <td class="right-align">{{ formatarValor(item.vlUnitarioProponenteIntegral) }}</td>
            
                                <td class="right-align">{{ formatarValor(item.vlReceitaPrevista) }}</td>
            
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="12" class="center-align">Sem detalhamento</td>
                            </tr>
                        </tbody>
                        <tfoot v-if="detalhamentos.length > 0" style="opacity: 0.8">
                            <tr>
                                <td><b>Totais</b></td>
                                <td class="right-align">{{ qtExemplaresTotal }}</td>
                                <!--Distribuicao gratuita -->
                                <td class="right-align">
                                {{ parseInt(qtGratuitaDivulgacaoTotal) + parseInt(qtGratuitaPatrocinadorTotal) + parseInt(qtGratuitaPopulacaoTotal)}}
                                </td>
                                <!--Preço Popular -->
                                <td class="right-align">{{ qtPopularIntegralTotal }}</td>
                                <td class="right-align">{{ qtPopularParcialTotal }}</td>
                                <td class="right-align"> -</td>
                                <!--Fim: Preço Popular -->
                                <td class="right-align">{{ qtProponenteIntegralTotal }}</td>
                                <td class="right-align">{{ qtProponenteParcialTotal }}</td>
                                <td class="right-align"> -</td>
                                <td class="right-align">{{ receitaPrevistaTotal }}</td>
                            </tr>
                        </tfoot>
                    </table>               
                </div>
            </li>
        </ul>
    </div>
    `,
    data: function () {
        return {
            detalhamentos: [],
        }
    },
    props: [
        'arrayDetalhamentos'
    ],
    computed: {
        // Total de exemplares
        qtExemplaresTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtExemplares']);
            }
            return total;
        },
        // Total de divulgação gratuita.
        qtGratuitaDivulgacaoTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtGratuitaDivulgacao']);
            }
            return total;
        },
        // Total de divulgação Patrocinador
        qtGratuitaPatrocinadorTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtGratuitaPatrocinador']);
            }
            return total;
        },
        // Total de divulgação gratuita.
        qtGratuitaPopulacaoTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtGratuitaPopulacao']);
            }
            return total;
        },
        //Preço Popular: Quantidade de Inteira
        qtPopularIntegralTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtPopularIntegral']);
            }
            return total;
        },
        //Preço Popular: Quantidade de meia entrada
        qtPopularParcialTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtPopularParcial']);
            }
            return total;
        },
        vlReceitaPopularIntegralTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                var vl = (this.detalhamentos[i]['vlReceitaPopularIntegral']);
                total += numeral(vl).value();
            }
            return numeral(total).format();
        },
        vlReceitaPopularParcialTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                var vl = (this.detalhamentos[i]['vlReceitaPopularParcial']);
                total += numeral(vl).value();
            }
            return numeral(total).format();
        },
        qtProponenteIntegralTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtProponenteIntegral']);
            }
            return total;
        },
        qtProponenteParcialTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                total += parseInt(this.detalhamentos[i]['qtProponenteParcial']);
            }
            return total;
        },
        vlReceitaProponenteIntegralTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                vl = (this.detalhamentos[i]['vlReceitaProponenteIntegral']);
                total += this.converterParaMoedaAmericana(vl);
            }
            return numeral(total).format();
        },
        vlReceitaProponenteParcialTotal: function () {
            total = 0;
            for (var i = 0; i < this.detalhamentos.length; i++) {
                var vl = (this.detalhamentos[i]['vlReceitaProponenteParcial']);
                total += this.converterParaMoedaAmericana(vl);
            }
            return numeral(total).format();
        },
        receitaPrevistaTotal: function () {
            var total = numeral();

            for (var i = 0; i < this.detalhamentos.length; i++) {
                var vl = this.detalhamentos[i]['vlReceitaPrevista'];
                total.add(parseFloat(vl));
            }
            return total.format();
        }
    },
    watch: {
        arrayDetalhamentos: function(value) {
            this.detalhamentos = value;
        }

    },
    mounted: function () {
        if (typeof this.arrayDetalhamentos != 'undefined') {
            this.iniciarCollapsible();
            this.detalhamentos = this.arrayDetalhamentos;
        }
    },
    methods: {
        fetch: function () {
            let vue = this;

            $3.ajax({
                type: "GET",
                url: "/proposta/visualizar/obter-detalhamento-plano-distribuicao",
                data: {
                    idPreProjeto: vue.idpreprojeto,
                }
            }).done(function (response) {
                vue.detalhamentos = response.data;
            });
        },
        converterParaMoedaAmericana: function (valor) {
            if (!valor)
                valor = '0';

            valor = valor.replace(/\./g, '');
            valor = valor.replace(/\,/g, '.');
            valor = parseFloat(valor);
            valor = valor.toFixed(2);

            if (isNaN(valor))
                valor = 0;

            return valor;
        },
        formatarValor: function (valor) {
            valor = parseFloat(valor);
            return numeral(valor).format();
        },
        iniciarCollapsible: function () {
            $3('.detalhamento-plano-distribuicao .collapsible').each(function () {
                $3(this).collapsible();
            });
        }
    }
});

