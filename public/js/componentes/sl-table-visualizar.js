Vue.component('sl-table-visualizar', {
    template:`
    <div>
        <table class="bordered">
            <thead>
                <th v-for="cab in dados.cols">
                    {{cab}} 
                </th>
            </thead>
            <tbody>
                <tr v-for="dado in dados.lines">
                    <td v-for="d in dado">
                        {{d}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    `,
    data: function() {
        return {
            dados:[]
        }
    },
    props: ['dados'],
    mounted: function() {
    },
    methods: {
        alerta: function() {
        }
    }
});
