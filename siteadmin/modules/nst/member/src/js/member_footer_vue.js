Vue.component('member-footer-view', {
    // language=HTML
    template:
    `
        <div class="clearfix form-actions">
            <div class="col-md-offset-3 col-md-9">
                <button class="btn btn-info" @click.prevent="saveMemberInfo">
                    <i class="fa fa-save bigger-110"></i>
                    Save
                </button>

                &nbsp; &nbsp;
                <button class="btn" type="reset">
                    <i class="fa fa-undo bigger-110"></i>
                    Reset
                </button>
            </div>
        </div>
    `,
    props: [
        'member_id',
        'member_type',
        'id'
    ],
    data: function() {
        return {

        };
    },
    created() {
    },
    mounted() {

    },
    computed: {

    },
    methods: {
        saveMemberInfo() {
            this.$root.$emit('saveMemberData');
        }
    }
});
