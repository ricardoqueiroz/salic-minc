somenteLeitura = parseInt(jQuery('#somenteLeitura').val());
stPrincipal = parseInt(jQuery('#stPrincipal').val());
IN2017 = parseInt(jQuery('#IN2017').val());
produtosSecundariosEmAnalise = parseInt(jQuery('#produtosSecundariosEmAnalise').val());
valorpossivelApresentacao = jQuery('#valorpossivelApresentacao').val();
IdPRONAC = jQuery('#IdPRONAC').val();
baseUrl = jQuery('#baseUrl').val();
vlSolicitado = jQuery('#vlSolicitado').val();
idProduto = jQuery('#idProduto').val();

$(document).ready(function(){
    // var parecer = null;
    carregarAnaliseDeConteudo();

    // salvar analise de conteudo
    $('#btSalvarAnaliseDeConteudos').click(function()
    {
        if (!somenteLeitura) {
            parecer = $("#ParecerDeConteudo").val().replace(/(<.*?>)|(&nbsp;)|(\s+)/g,'');
            console.log('arecer', parecer);
        }

        erros = 0;

        // if (IN2017 && stPrincipal && (produtosSecundariosEmAnalise == 0)){
        //     // var acoesRelevantes = $('#dsAcaoAlcanceProduto').val().replace(/(<.*?>)|(&nbsp;)|(\s+)/g,'');
        //     if( acoesRelevantes.length  < 11){
        //         alertar('As a\xE7\xF5es relevantes devem ter no m\xEDnimo 11 caracteres!');
        //         erros ++;
        //     }
        // }

        if( parecer.length  < 11){
            alertar('O parecer t&eacute;cnico deve ter no m\xEDnimo 11 caracteres!');
            erros ++;
        }

        if (!erros) {
            $('#formAnaliseConteudo').submit();
        }
        return false;
    });

    window.setTimeout(carregarTextAreaCKEditor(), 1000);

    $("#valorpossivelApresentacao").html(valorpossivelApresentacao);
    var grupoAtivo = $("#grupoAtivo").val();
    if(grupoAtivo == "93"){
        desabilitaFormConteudo(true);
        $("#divAnaliseCusto a").click(function() { return false; });
    }

    var cont = 0;
    $('.class_relatorio').each(function(){
        var contAux = cont;
        $('#abrir_fechar'+contAux).click(function(){
            $('#div_relatorio'+contAux).toggle('slow');
        });
        cont++;
    });

    $('.linkrelatorio').click(function(){
        var este = this;
        var cont = $(este).attr('cont');
        $('#div_relatorio'+cont).html('<table class="tabelaRelatorio" cellspacing="1" style="margin: 0;margin-left: 52px; width: 90%;  padding: 0px;"><tr><td align="center"><img src="../public/img/ajax.gif"/>&nbsp;Carregando...</td><tr></table>');
        $('#div_relatorio'+cont).css('display','');
        $.post($(este).attr('link'),{nrRelatorio: cont, idPRONAC:IdPRONAC},function(data){
            $('#div_relatorio'+$(este).attr('cont')).html(data);
        });
    });
    $(".icn_menos").click(function(){
        var tipo = $(this).attr('tipo');

        var aberto = $(this).attr('aberto');
        if(aberto == 'true'){
            adisplay = 'none';
            $(this).attr('aberto','false');
            $(this).removeClass('icn_menos').addClass('icn_mais');
        }
        else{
            adisplay = '';
            $(this).attr('aberto','true');
            $(this).removeClass('icn_mais').addClass('icn_menos');
        }
        if(tipo == 'fonte'){
            fonte = $(this).attr('fonte');
            $(".master[fonte='"+fonte+"']").css('display', adisplay);
            $(".clickproduto").removeClass('icn_mais').addClass('icn_menos');
        }
        if(tipo == 'produto'){
            fonte = $(this).attr('fonte');
            produto = $(this).attr('produto');
            $(".produto[produto='"+produto+"'][fonte='"+fonte+"']").css('display', adisplay);
        }
        if(tipo == 'etapa'){
            fonte = $(this).attr('fonte');
            produto = $(this).attr('produto');
            etapa = $(this).attr('etapa');
            $(".etapa[produto='"+produto+"'][etapa='"+etapa+"'][fonte='"+fonte+"']").css('display', adisplay);
        }
        if(tipo == 'cidade'){
            fonte = $(this).attr('fonte');
            produto = $(this).attr('produto');
            etapa = $(this).attr('etapa');
            cidade = $(this).attr('cidade');
            $(".cidade[produto='"+produto+"'][etapa='"+etapa+"'][cidade='"+cidade+"'][fonte='"+fonte+"']").css('display', adisplay);
        }
    });

    if(!IN2017) {
        APPescolherLei_8313();
        carregarEnquadramento();
    }

}); //fecha document.ready


function carregarTextAreaCKEditor() {
    if (somenteLeitura == 0) {
        // CKEDITOR.replace( 'ParecerDeConteudo', { toolbar: [] } );
        // console.log('teste1', $('#ParecerDeConteudo').val());
    } else {
        var parecer = $('#ParecerDeConteudo').val();
        // $('#ParecerDeConteudo').before(parecer).remove();
        $('#ParecerFavoravel').attr('disabled', 1);
        $('#ParecerDesfavoravel').attr('disabled', 1);
        $('#btSalvarAnaliseDeConteudo').remove();
        if (jQuery('#IN2017').val() && jQuery('#stPrincipal').val() && jQuery('#produtosSecundariosEmAnalise').val() == 0) {
            var dsAcaoAlcanceProduto = $('#dsAcaoAlcanceProduto').val();
            // $('#dsAcaoAlcanceProduto').before(dsAcaoAlcanceProduto).remove();
        }
    }

    if (jQuery('#stPrincipal').val() == 1 && jQuery('#produtosSecundariosEmAnalise').val() == 0) {
        if (jQuery('#IN2017').val() == 1 && jQuery('#stPrincipal').val() == 1) {
            // $(dsAcaoAlcanceProduto)..val().replace( 'dsAcaoAlcanceProduto', { toolbar: [] } );
        }
        // CKEDITOR.replace( 'dsConsolidacao', { toolbar: [] } );
    }
}

function carregarAnaliseDeConteudo()
{
    obj = {
        stAcao: 1,
        idPronac: IdPRONAC,
        idProduto: idProduto,
        stPrincipal: stPrincipal
    };

    recuperaFormulario('/parecer/analise-inicial/obter-analise-conteudo', obj);
    return true;
}

function recuperaFormulario(url,params)
{
    if(params.length < 1) {
        params = {};
    }
    var btSalvar = false;

    $('#formAnaliseConteudo').prepend('<div class="loader centro">Aguarde carregando dados...</div>')
    $('#formAnaliseConteudo').find('fieldset').hide();

    $.post(url,params,function(data) {
        // console.log(data);
        if(data){
            $('#formAnaliseConteudo').find('input, select, textarea').not('input[type="hidden"]').each(function(key, object){
                // console.log(key, object);
                if($(object).attr('type') == 'radio'){
                    if($(object).val() == data[$(object).prop('name')]){
                        btSalvar = true;
                        $(object).prop('checked','true');
                    }
                }else{
                    if($(object).attr('type') == 'checkbox'){
                        if(data[$(object).attr('name')] == 1){
                            btSalvar = true;
                            $(object).prop('checked','true');
                        }
                    }else{
                        btSalvar = true;
                        console.log(data[$(object).prop('name')]);
                        $(object).val(data[$(object).prop('name')]);
                    }
                }
            });
            $('#stAcao').val(2);
        }else{
            $('#stAcao').val(3);
        }

        var editorRico = $("#ParecerDeConteudo").editorRico({
            altura: 200,
            isDesabilitarEdicao: somenteLeitura
        });

        $("#formAnaliseConteudo div.loader").remove();
        $('#formAnaliseConteudo').find('fieldset').show();
    },'json');
}

function desabilitaFormConteudo(habilitar){
    $('#formAnaliseConteudo').find('input, select, textarea').each(function(key, object){
        if(habilitar){
            $(object).attr('disabled','true');
        }else{
            $(object).removeAttr('disabled');
        }
    });
}
