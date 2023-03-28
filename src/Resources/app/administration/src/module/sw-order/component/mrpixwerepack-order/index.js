import template from './mrpixwerepack-order.html.twig';
import  './mrpixwerepack-order.scss';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('sw-customer-werepack', {
    template,

    data() {
        return {
            weRepackOrder: null
        }
    },

    inject: ['repositoryFactory'],

    props: {
        order: {
            type: Object,
            required: true
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('mp_repack_order');
        }
    },

    created() {
        const criteria = (new Criteria())
            .addAssociation('order')
            .addAssociation('promotionIndividualCode')
            .addFilter(Criteria.equals("orderId", this.order.id));

        this.repository.search(criteria, Context.api)
            .then(result => {
                this.weRepackOrder = result.first();
                console.log(this.weRepackOrder);
            });
    }
});