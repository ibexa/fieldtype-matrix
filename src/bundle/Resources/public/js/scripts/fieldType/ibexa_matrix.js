(function (global, doc, ibexa) {
    const SELECTOR_REMOVE_MATRIX_BULK = '.ibexa-btn--bulk-remove-matrix';
    const SELECTOR_REMOVE_MATRIX_ENTRY = '.ibexa-btn--remove-matrix-entry';
    const SELECTOR_ADD_MATRIX_ENTRY = '.ibexa-btn--add-matrix-entry';
    const SELECTOR_MATRIX_ENTRIES_CONTAINER = '.ibexa-table__body';
    const SELECTOR_MATRIX_ENTRY_TEMPLATE = '.ibexa-data-source__entry-template';
    const SELECTOR_MATRIX_ENTRY_CHECKBOX = '.ibexa-table__ibexa_matrix-entry-checkbox';
    const SELECTOR_MATRIX_ENTRY = '.ibexa-table__matrix-entry';
    const SELECTOR_FIELD = '.ibexa-field-edit--ibexa_matrix';
    const NUMBER_PLACEHOLDER = /__index__/g;

    if (!doc.querySelector(SELECTOR_FIELD)) {
        return;
    }

    class IbexaMatrixValidator extends ibexa.BaseFieldValidator {
        /**
         * Adds an item.
         *
         * @method addItem
         * @param {Event} event
         * @memberof IbexaMatrixValidator
         */
        addItem(event) {
            const matrixNode = event.target.closest(SELECTOR_FIELD);
            const template = matrixNode.querySelector(SELECTOR_MATRIX_ENTRY_TEMPLATE).innerHTML;
            const node = matrixNode.querySelector(SELECTOR_MATRIX_ENTRIES_CONTAINER);

            node.insertAdjacentHTML('beforeend', this.setIndex(matrixNode, template));

            this.reinit();
            this.updateDisabledState(matrixNode);

            doc.body.dispatchEvent(new CustomEvent('ibexa-inputs:added'));
        }

        findCheckedEntries(node) {
            return node.querySelectorAll(`${SELECTOR_MATRIX_ENTRY_CHECKBOX}:checked`);
        }

        getNextIndex(parentNode) {
            const node = parentNode.querySelector(SELECTOR_MATRIX_ENTRIES_CONTAINER);

            return node.dataset.nextIndex++;
        }

        /**
         * Sets an index to template.
         *
         * @method setIndex
         * @param {HTMLElement} parentNode
         * @param {String} template
         * @returns {String}
         * @memberof IbexaMatrixValidator
         */
        setIndex(parentNode, template) {
            return template.replace(NUMBER_PLACEHOLDER, this.getNextIndex(parentNode));
        }

        /**
         * Updates the disable state.
         *
         * @method updateDisabledState
         * @param {HTMLElement} parentNode
         * @memberof IbexaMatrixValidator
         */
        updateDisabledState(parentNode) {
            const isEnabled = this.findCheckedEntries(parentNode).length > 0;
            const methodName = isEnabled ? 'removeAttribute' : 'setAttribute';

            parentNode.querySelectorAll(SELECTOR_REMOVE_MATRIX_BULK).forEach((btn) => btn[methodName]('disabled', !isEnabled));
        }

        /**
         * Removes an item.
         *
         * @method removeItem
         * @param {Event} event
         * @memberof IbexaMatrixValidator
         */
        removeItems(event) {
            const matrixNode = event.target.closest(SELECTOR_FIELD);

            this.findCheckedEntries(matrixNode).forEach((element) => element.closest(SELECTOR_MATRIX_ENTRY).remove());
            this.updateDisabledState(matrixNode);

            this.reinit();
        }

        removeItem(event) {
            const entry = event.target.closest(SELECTOR_MATRIX_ENTRY);
            const matrixNode = event.target.closest(SELECTOR_FIELD);

            entry.remove();

            this.updateDisabledState(matrixNode);
            this.reinit();
        }

        checkEntry(event) {
            const matrixNode = event.target.closest(SELECTOR_FIELD);

            this.updateDisabledState(matrixNode);
        }

        /**
         * Attaches event listeners based on a config.
         *
         * @method init
         * @memberof IbexaMatrixValidator
         */
        init() {
            super.init();

            doc.querySelectorAll(this.fieldSelector).forEach((field) => {
                const rowsCount = field.querySelectorAll(SELECTOR_MATRIX_ENTRY).length;
                const minimumRows = parseInt(field.querySelector(SELECTOR_MATRIX_ENTRIES_CONTAINER).dataset.minimumRows, 10);
                const { emptyEntriesAdded } = field.dataset;

                field.dataset.emptyEntriesAdded = true;

                if (!emptyEntriesAdded) {
                    for (let i = 0; i < minimumRows - rowsCount; i++) {
                        this.addItem({ target: field });
                    }
                }

                this.updateDisabledState(field);
            });
        }
    }

    const validator = new IbexaMatrixValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                isValueValidator: false,
                selector: SELECTOR_MATRIX_ENTRY_CHECKBOX,
                eventName: 'click',
                callback: 'checkEntry',
            },
            {
                isValueValidator: false,
                selector: SELECTOR_REMOVE_MATRIX_BULK,
                eventName: 'click',
                callback: 'removeItems',
            },
            {
                isValueValidator: false,
                selector: SELECTOR_ADD_MATRIX_ENTRY,
                eventName: 'click',
                callback: 'addItem',
            },
            {
                isValueValidator: false,
                selector: SELECTOR_REMOVE_MATRIX_ENTRY,
                eventName: 'click',
                callback: 'removeItem',
            },
        ],
    });

    validator.init();

    global.ibexa.fieldTypeValidators = global.ibexa.fieldTypeValidators ? [...global.ibexa.fieldTypeValidators, validator] : [validator];
})(window, window.document, window.ibexa);
