import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';

export default class MrpixWeRepackPlugin extends Plugin {

    static options = {
        checkboxSelector: '#weRepackShipping'
    };

    init() {
        this._getCheckbox();

        if (!this._checkbox) {
            throw new Error(`No form found for the plugin: ${this.constructor.name}`);
        }
        this._client = new HttpClient();

        const onCheckboxChange = this._onCheckboxChange.bind(this);
        this._checkbox.addEventListener('change', onCheckboxChange)
    }

    _onCheckboxChange(event) {
        const formData = FormSerializeUtil.serialize(this.el);
        const data = {
            'isSelected': event.target.checked
        };

        this._client.post(this.el.action, formData, this._handleResult.bind(this), 'application/json');
    }

    _handleResult(data) {
        console.log(data);
    }

    _getCheckbox() {
        this._checkbox = document.querySelector(this.options.checkboxSelector);
    }
}