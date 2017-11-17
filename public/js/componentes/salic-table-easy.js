Vue.component('salic-table-easy', {
    template:`
    <div>
        <table class="bordered">
            <thead>
                <th v-html="cab" v-for="cab in table.cols">
                </th>
            </thead>
            <tbody>
                <tr v-for="dado in table.lines">
                    <td v-html="d" v-for="d in dado">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    `,
    data: function() {
        return {
            table:[]
        }
    },
    props: ['dados'],
    mounted: function() {
        this.table = this.dados;
    }
});
