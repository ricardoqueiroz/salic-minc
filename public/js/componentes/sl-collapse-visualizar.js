Vue.component('sl-collapse-visualizar', {
    template:`
        <div>
            <h4>{{pronac}} - {{nome}}</h4>
            <ul v-show="dados" class="collapsible" data-collapsible="accordion">
                <li v-for="dado in dados">
                  <div class="collapsible-header">{{dado.title}}</div>
                  <div class="collapsible-body">
                    <salic-table-easy 
                        v-bind:dados="dado" 
                        v-bind:thead="true"
                        v-bind:tfoot="true"
                        class="bordered"
                    >
                    </salic-table-easy>
                  </div>
                </li>
            </ul>
            <div v-show="!dados">carregando...</div>
        </div>
    `,
    data: function() {
        return {
            dados:[]
        }
    },
    created: function() {
        vue = this;
        bus.$on('id-selected', function (obj) {
            vue.idpronac = obj.idpronac;
            vue.nome = obj.nome;
            vue.pronac = obj.pronac;
            vue.alerta();
        });
    },
    props: ['idpronac', 'pronac', 'nome'],
    mounted: function() { },
    methods: {
        alerta: function() {
            vue = this;
            vue.dados = ''; 
            $3.ajax({
                url: '/prestacao-contas/visualizar-projeto/dados-projeto?idPronac=' + vue.idpronac
            })
            .done( function(data) {
                vue.dados = data; 
            });
        }
    }
});
