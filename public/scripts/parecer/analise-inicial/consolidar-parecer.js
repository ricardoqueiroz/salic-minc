stPrincipal = parseInt(jQuery('#stPrincipal').val());
IN2017 = parseInt(jQuery('#IN2017').val());
produtosSecundariosEmAnalise = parseInt(jQuery('#produtosSecundariosEmAnalise').val());


$(document).ready(function(){

    $("textarea.editor").each(function () {

        $(this).editorRico({
            altura: 200,
            maxchar: 8000,
            isLimitarCarateres : true,
            isDesabilitarEdicao: 0
        });
    });

    $('#areaCultural').change(function() {
        carregarSegmento();
    });

    $('#segmentoCultural').change(function() {
        carregarEnquadramento();
    });

    $('#btSalvarConsolidacao').click(function() {

        if (IN2017 && stPrincipal && (produtosSecundariosEmAnalise == 0)){
            $('#consolidacaoDsAcaoAlcanceProduto').val($('#dsAcaoAlcanceProduto').val());
        }

        var i = 0;
        $('.obrigatorio').each(function(){
            if($.trim($(this).val()) == ''){
                i++;
            }
        });

        var boolValid = false;
        $('.obrigatorioRadio').each(function() {
            if ($(this).is(':checked') ) {
                boolValid = true;
            }
        });

        if (typeof tinyMCE == 'object') {
            tinyMCE.triggerSave();
        }

        var texto = $('#dsConsolidacao').val().replace(/(<.*?>)|(&nbsp;)|(\s+)/g,'');

        if (texto == '' || texto == 'Digiteseutextoaqui...'){
            i++;
        }

        if(i > 0 || !boolValid) {
            $("#camposObrigatorios").dialog("destroy");
            $("#camposObrigatorios").html("Favor preencher os dados obrig&aacute;torios!");
            $("#camposObrigatorios").dialog({
                resizable: false,
                title: 'Alerta!',
                width:320,
                height:160,
                modal: true,
                buttons : {
                    'OK' : function(){
                        $(this).dialog('close');
                    }
                }
            });
            $('.ui-dialog-titlebar-close').remove();

        }
    });

});

function carregarSegmento() {
    $('#segmentoCultural').html('<option value=""> - Carregando - </option>');
    $.ajax({
        type: 'POST',
        url: '/default/segmento/combo',
        data: {
            id: $('#areaCultural').val()
        },
        success: function(dados) {
            $('#segmentoCultural').find('option').remove();
            $('#segmentoCultural').append(dados);
        }
    });
}

function carregarEnquadramento(){
    var listaCodSegmento = [ '11','12','13','14','15','17','23','26', '28', '2A','2B','2E','2F','2G','2H','2I','2J','2K','2L','32','33','36','4B','4D','5A','5D','5E','5F','5G','5H','5I','5J','5K','5L','5M','5N','5O','5P','62','65','68','6C','6D','6E','6G','6H' ];
    if(in_array($('#segmentoCultural').val(), listaCodSegmento)){
        $('#enquadramentoProjeto').val('2');
        $('#enquadramentoText').html('Artigo 18');
    } else {
        $('#enquadramentoProjeto').val('1');
        $('#enquadramentoText').html('Artigo 26');
    }
}