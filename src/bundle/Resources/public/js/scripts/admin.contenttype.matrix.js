(function (global, doc) {
    const SELECTOR_SETTINGS_COLUMNS = '.ibexa-matrix-settings__columns';
    const SELECTOR_COLUMN = '.ibexa-matrix-settings__column';
    const SELECTOR_COLUMNS_CONTAINER = '.ibexa-table__body';
    const SELECTOR_COLUMN_CHECKBOX = '.ibexa-matrix-settings__column-checkbox';
    const SELECTOR_ADD_COLUMN = '.ibexa-btn--add-column';
    const SELECTOR_REMOVE_COLUMN = '.ibexa-btn--remove-column';
    const SELECTOR_TEMPLATE = '.ibexa-matrix-settings__column-template';
    const NUMBER_PLACEHOLDER = /__number__/g;
    const getNextIndex = (parentNode) => {
        return parentNode.dataset.nextIndex++;
    };
    const findCheckedColumns = (parentNode) => {
        return parentNode.querySelectorAll(`${SELECTOR_COLUMN_CHECKBOX}:checked`);
    };
    const updateDisabledState = (parentNode) => {
        const isEnabled = findCheckedColumns(parentNode).length > 0;
        const methodName = isEnabled ? 'removeAttribute' : 'setAttribute';

        parentNode.querySelectorAll(SELECTOR_REMOVE_COLUMN).forEach((btn) => btn[methodName]('disabled', !isEnabled));
    };
    const addItem = (event) => {
        const settingsNode = event.target.closest(SELECTOR_SETTINGS_COLUMNS);
        const template = settingsNode.querySelector(SELECTOR_TEMPLATE).innerHTML;
        const node = settingsNode.querySelector(SELECTOR_COLUMNS_CONTAINER);
        const emptyPlaceholder = node.querySelector('.ibexa-table__empty-table-cell');

        if (emptyPlaceholder) {
            emptyPlaceholder.closest('.ibexa-table__row').remove();
        }

        node.insertAdjacentHTML('beforeend', template.replace(NUMBER_PLACEHOLDER, getNextIndex(node)));

        initColumns(settingsNode);

        node.closest('.ibexa-table').dispatchEvent(new CustomEvent('ibexa-refresh-main-table-checkbox'));
    };
    const removeItems = (event) => {
        const settingsNode = event.target.closest(SELECTOR_SETTINGS_COLUMNS);

        findCheckedColumns(settingsNode).forEach((checkbox) => {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
            checkbox.closest(SELECTOR_COLUMN).remove();
        });

        initColumns(settingsNode);
    };
    const checkColumn = (event) => {
        const settingsNode = event.target.closest(SELECTOR_SETTINGS_COLUMNS);

        updateDisabledState(settingsNode);
    };
    const initColumns = (parentNode) => {
        updateDisabledState(parentNode);

        parentNode.querySelectorAll(SELECTOR_COLUMN_CHECKBOX).forEach((checkbox) => {
            checkbox.removeEventListener('change', checkColumn, false);
            checkbox.addEventListener('change', checkColumn, false);
        });

        parentNode.querySelector('.ibexa-table').dispatchEvent(new CustomEvent('ibexa-refresh-main-table-checkbox'));
    };
    const initComponent = (container) => {
        container.querySelector(SELECTOR_ADD_COLUMN).addEventListener('click', addItem, false);
        container.querySelector(SELECTOR_REMOVE_COLUMN).addEventListener('click', removeItems, false);

        initColumns(container);
    };

    doc.querySelectorAll(SELECTOR_SETTINGS_COLUMNS).forEach((container) => {
        initComponent(container);
    });

    doc.body.addEventListener(
        'ibexa-drop-field-definition',
        (event) => {
            const { nodes } = event.detail;

            nodes.forEach((container) => {
                if (!container.querySelector(SELECTOR_SETTINGS_COLUMNS)) {
                    return;
                }

                initComponent(container);
            });
        },
        false,
    );
})(window, document);
