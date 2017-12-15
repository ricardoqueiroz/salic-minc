somenteLeitura = parseInt(jQuery('#somenteLeitura').val());
stPrincipal = parseInt(jQuery('#stPrincipal').val());
IN2017 = parseInt(jQuery('#IN2017').val());
produtosSecundariosEmAnalise = parseInt(jQuery('#produtosSecundariosEmAnalise').val());
valorpossivelApresentacao = jQuery('#valorpossivelApresentacao').val();
IdPRONAC = jQuery('#IdPRONAC').val();
baseUrl = jQuery('#baseUrl').val();
vlSolicitado = jQuery('#vlSolicitado').val();
idProduto = jQuery('#idProduto').val();

$3(document).ready(function($){

    $("body")
        .on('mouseover mouseout', 'tr.item',function(){
            $(this).toggleClass('indigo lighten-5');
        })
        .on('click', 'tr.item', function() {
            $(this).toggleClass('deep-purple lighten-4');
        })
    ;

});


function AlterarItem(idPlanilhaProjeto, idPronac, idProduto, stPrincipal) {
    var elmSugerirItem = $('#modal-sugerir-item-custo');

    elmSugerirItem.html('<br><center>Aguarde, carregando dados...<br><img src="/public/img/ajax.gif" /></center><br>');

    elmSugerirItem.dialog({
        title: "Alterar Item",
        resizable: false,
        width: 800,
        height: 600,
        modal: false,
        autoOpen: true,
        closeOnEscape: true
    });

    $.post(
        '/parecer/analise-inicial/sugerir-item-custo-modal',
        {
            idPlanilhaProjeto: idPlanilhaProjeto,
            idPronac: idPronac,
            idProduto: idProduto,
            stPrincipal: stPrincipal
        },
        function (data) {
            elmSugerirItem.html(data);
            elmSugerirItem.dialog({
                title: "Alterar Item",
                resizable: false,
                width: 800,
                height: 600,
                modal: false,
                autoOpen: true,
                closeOnEscape: true,
                buttons: {
                    'Cancelar': function () {
                        $(this).dialog('close');
                    },
                    'Salvar': function () {
                        var texto = $('#justificativa' + $('#idPlanilhaProjeto').val()).val();
                        texto = texto.replace(/^\s+|\s+$/g, ""); //retirando espacos
                        if (texto.length <= 0 || texto == "") {
                            alertar('Dados obrigat\xF3rios n\xE3o informados.');
                            return false;
                        }
                        else {
                            var vlSugerido = $('#calcular_total' + $('#idPlanilhaProjeto').val()).html().replace(/\R\$\s+/g, '');
                            $('#totalcalculos' + $('#idPlanilhaProjeto').val()).val(vlSugerido);

                            var dados = $('#formSalvarItem').serialize();
                            $(this).append('<br><center>Aguarde, salvando informa\xE7\xF5es...<br><img src="' + baseUrl + '"/public/img/ajax.gif" /></center><br>');
                            $.post(
                                $('#formSalvarItem').attr('action'),
                                dados,
                                function (data) {
                                    if (data.status == '1') {
                                        var resposta = data;
                                        $('#item' + $('#idPlanilhaProjeto').val() + ' .JustParecerista').html($("#justificativa" + $('#idPlanilhaProjeto').val()).val());
                                        $('#item' + $('#idPlanilhaProjeto').val() + ' .ValorSugerido').html($("#calcular_total" + $('#idPlanilhaProjeto').val()).html());
                                        somarValores();

                                        //atualiza novo valor possivel de remanejamento
                                        /* var vlSolicitado = vlSolicitado; */
                                        var vlTotalSugerido = recuperaTotalSugerido();
                                        var valorpossivel = vlSolicitado - vlTotalSugerido;

                                        $("#valorpossivel").val(valorpossivel);

                                        //atualiza campo de apresentacao do novo valor possivel de remanejamento
                                        var valorpossivelApresentacao = formatarParaReal(valorpossivel);
                                        $("#valorpossivelApresentacao").html(valorpossivelApresentacao);

                                        elmSugerirItem.dialog('close');
                                        Materialize.toast(data.msg, 4000, 'green light-green accent-1 black-text');
                                    } else {
                                        Materialize.toast(data.msg, 4000, 'red accent-1');
                                    }
                                }
                            );
                        }
                    }
                }
            });
        }
    );
}

function abrirfechar(elemento){
    $('#'+elemento).toggle();
}

function AbrirFecharPlanilha(elemento)
{
    $('.' + elemento).toggle();
    if ($('#' + elemento).hasClass('icn_mais'))
    {
        $('#' + elemento).addClass('icn_menos');
        $('#' + elemento).removeClass('icn_mais');
    }
    else
    {
        $('#' + elemento).addClass('icn_mais');
        $('#' + elemento).removeClass('icn_menos');
    }
}

function janelaAlerta(mensagem,funcaoAdcional)
{
    if(funcaoAdcional==undefined)
    {
        funcaoAdcional = function(){}
    }
    var nomeJanelaAlerta =   janelaObj({
        parametros : {
            width:      400,
            autoOpen:   false,
            resizable:  false,
            modal:      true,
            buttons: {
                OK: function() {
                    funcaoAdcional();
                    $(this).dialog('close');
                }
            }
        },
        removerBtFechar:true,
        title : 'Alerta'
    });
    nomeJanelaAlerta.divConteudo.html(mensagem);
    nomeJanelaAlerta.abrirJanela();

    return nomeJanelaAlerta;
}

function janelaObj(dados){
    var divConteudo = $('<div></div>')
        .attr('title',dados.title)
        .appendTo('body');
    var novaJanela =
    {
        divConteudo : divConteudo,
        removerBtFechar: true,
        parametros : {autoOpen: false},
        iniciarJanela : function(dados){
            this.refineParametrosObj(dados);

            this.divConteudo.dialog(this.parametros);
        },
        abrirJanela:function(){
            this.divConteudo.dialog('open');
            if(this.removerBtFechar)
                $('.ui-dialog-titlebar-close').remove();
        },
        fecharJanela:function(){
            this.divConteudo.dialog('close');
            this.divConteudo.remove();
        },
        refineParametrosObj : function(data){
            if(data!= undefined)
                for(var j in data){
                    this[j]=data[j];
                }
        }
    }
    novaJanela.iniciarJanela(dados);
    return novaJanela;
}


function recuperaTotalSugerido()
{
    // pega o valor total do relator
    var valor = $('.valorTotalSugerido').html();
    valor = valor.replace('R$ ', '');

    // retira os pontos e as virgulas, deixando somente numeros
    valor = valor.replace(/\D/g, "");
    valor = valor.replace(/(\d{0})(\d)/, "$1$2");

    // adiciona o ponto na casa decimal
    valor = valor.replace(/(\d)(\d{2})$/, "$1.$2");

    // converte para float
    valor = parseFloat(valor);

    return valor;
}

// formata para real
function formatarParaReal(valor)
{
    valor = (parseFloat(valor)).toFixed(2);
    valor = valor.replace(/\D/g, "");
    valor = valor.replace(/(\d)(\d{2})$/, "$1,$2");
    valor = valor.replace(/(\d+)(\d{3},\d{2})$/g, "$1.$2");

    var q = (valor.length - 3) / 3; // quantidade caracteres
    var c = 0; // contador
    while (q > c)
    {
        c++;
        valor = valor.replace(/(\d+)(\d{3}.*)/, "$1.$2");
    }
    valor = valor.replace(/^(0+)(\d)/g, "$2");
    valor = 'R$ ' + valor;

    return valor;
}