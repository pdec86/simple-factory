<template>
    <div class="container">
        <div class="row">
            <div class="col">
                <v-alert
                    v-if="apiError"
                    density="compact"
                    type="warning"
                    title="Warning"
                    :text="apiError"
                ></v-alert>

                <v-menu
                    open-on-hover
                    >
                    <template v-slot:activator="{ props }">
                        <v-btn
                        color="primary"
                        v-bind="props"
                        >
                        Menu
                        </v-btn>
                    </template>

                    <v-list>
                        <v-list-item
                            v-for="(item, index) in menuItems"
                            :key="index"
                            >
                            <v-list-item-title><a :href="item.href">{{ item.title }}</a></v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>

                <v-data-table
                    v-model:expanded="productVariantsExpanded"
                    :items="products"
                    :headers="headers"
                    :sort-by="[{ key: 'name', order: 'asc' }]"
                    item-key="id"
                    items-per-page="10">
                    
                    <template v-slot:item.name="{ item }">
                        <span class="productName" @click="productVariants(item.id)">{{ item.name }}</span>
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            size="small"
                            class="me-2"
                            @click="editItem(item)"
                        >
                            mdi-pencil
                        </v-icon>

                        <v-icon
                            size="small"
                            class="me-2"
                            @click="createVariant(item)"
                        >
                            mdi-pen-plus
                        </v-icon>
                        <!-- mdi-basket-fill -->
                    </template>

                    <template v-slot:expanded-row="{ columns, item }">
                    <tr v-for="variant in productVariantsList" class="productVariant">
                        <td>{{ variant.length }} x {{ variant.width }} x {{ variant.height }}</td>
                        <td :colspan="columns.length - 2">
                            {{ variant.name }} ({{ variant.codeEan.code }})
                        </td>
                        <td>
                            <v-icon
                                size="small"
                                class="me-2"
                                @click="buyItem(item, variant.codeEan.code)"
                            >
                                mdi-basket-fill
                            </v-icon>
                        </td>
                    </tr>
                    </template>

                    <template v-slot:top>
                    <v-toolbar
                        flat
                    >
                        <v-toolbar-title>List of products</v-toolbar-title>
                        
                        <v-divider
                            class="mx-4"
                            inset
                            vertical
                        ></v-divider>
                        
                        <v-spacer></v-spacer>
                        
                        <v-dialog
                            v-model="dialog"
                            max-width="500px"
                        >

                        <template v-slot:activator="{ props }">
                            <v-btn
                                color="primary"
                                dark
                                class="mb-2"
                                v-bind="props"
                            >
                            New product
                            </v-btn>
                        </template>

                        <v-card>
                            <v-card-title>
                            <span class="text-h5">{{ formTitle }}</span>
                            </v-card-title>

                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                        >
                                            <v-text-field
                                                ref="editedItemNameRef"
                                                v-model="editedItem.name"
                                                label="Product name"
                                                :rules="[rules.required]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                        >
                                            <v-textarea
                                                v-model="editedItem.description"
                                                label="Description"
                                            ></v-textarea>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>

                                <v-btn
                                    color="blue-darken-1"
                                    variant="text"
                                    @click="close"
                                >
                                    Cancel
                                </v-btn>
                                <v-btn
                                    :disabled="!editedItemNameRef?.isValid"
                                    color="blue-darken-1"
                                    variant="text"
                                    @click="save"
                                >
                                    Save
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                        </v-dialog>

                        <v-dialog v-model="dialogBuy" max-width="500px">
                        <v-card>
                            <v-card-title class="text-h5">How many items would you like to buy?</v-card-title>
                            <v-card-text>{{ editedItem.name }}</v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-text-field
                                    ref="buyQuantityRef"
                                    v-model="buyVariant.quantity"
                                    label="Quantity"
                                    :rules="[rules.required, rules.positiveNumber]"
                                    type="number"
                                ></v-text-field>
                                <v-btn :disabled="!buyQuantityRef?.isValid" color="blue-darken-1" variant="text" @click="buy">Buy</v-btn>
                                <v-spacer></v-spacer>
                            </v-card-actions>
                        </v-card>
                        </v-dialog>

                        <v-dialog v-model="dialogCreateVariant" max-width="900px">
                        <v-card>
                            <v-card-title class="text-h5">Create product specific model</v-card-title>
                            <v-card-text>{{ editedItem.name }}</v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-container>
                                    <v-row>
                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                        >
                                            <v-text-field
                                                ref="variantNameRef"
                                                v-model="newProductVariant.name"
                                                label="Model name"
                                                :rules="[rules.required]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                        >
                                            <v-text-field
                                                ref="variantEANRef"
                                                v-model="newProductVariant.codeEan.code"
                                                label="Code EAN (13)"
                                                :rules="[rules.codeEan]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col
                                            cols="12"
                                            sm="6"
                                            md="4"
                                        >
                                            <v-textarea
                                                v-model="newProductVariant.length"
                                                label="Length (format '0.00')"
                                                :rules="[rules.dimension]"
                                            ></v-textarea>
                                        </v-col>

                                        <v-col
                                            cols="12"
                                            sm="6"
                                            md="4"
                                        >
                                            <v-textarea
                                                v-model="newProductVariant.width"
                                                label="Width (format '0.00')"
                                                :rules="[rules.dimension]"
                                            ></v-textarea>
                                        </v-col>

                                        <v-col
                                            cols="12"
                                            sm="6"
                                            md="4"
                                        >
                                            <v-textarea
                                                v-model="newProductVariant.height"
                                                label="Height (format '0.00')"
                                                :rules="[rules.dimension]"
                                            ></v-textarea>
                                        </v-col>
                                    </v-row>
                                </v-container>
                                <v-btn :disabled="!variantNameRef?.isValid || !variantEANRef?.isValid"
                                    color="blue-darken-1" variant="text" @click="createVariantSave">Create model</v-btn>
                                <v-spacer></v-spacer>
                            </v-card-actions>
                        </v-card>
                        </v-dialog>
                    </v-toolbar>
                    </template>
                </v-data-table>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, reactive, watch, computed, toRaw } from 'vue';
    import { useStore } from 'vuex';

    const headers = [
        { title: 'Name', value: 'name', sortable: true, 'expand-on-click': true },
        { title: 'Description', value: 'description', sortable: false },
        { title: 'Actions', key: 'actions', sortable: false },
    ];

    const menuItems = [
        {title: 'Factory', href: '/factory'},
        {title: 'Warehouse', href: '/warehouse'},
    ];

    const apiError = ref(null);
    const buyQuantityRef = ref(null);
    const editedItemNameRef = ref(null);
    
    const variantNameRef = ref(null);
    const variantEANRef = ref(null);

    const productVariantsExpanded = reactive([]);

    const dialog = ref(false);
    const dialogBuy = ref(false);
    const dialogCreateVariant = ref(false);

    // const buyQuantity = ref(0);
    const editedIndex = ref(-1);
    const editedItem = reactive({
        name: '',
        description: '',
    });
    const defaultItem = reactive({
        name: '',
        description: '',
    });
    const newProductVariant = reactive({
        productId: null,
        name: '',
        codeEan: {code: ''},
        length: '0.0',
        width: '0.0',
        height: '0.0'
    });
    const newProductVariantDefault = reactive({
        productId: null,
        name: '',
        codeEan: {code: ''},
        length: '0.0',
        width: '0.0',
        height: '0.0'
    });

    const buyVariant = reactive({
        productId: null,
        codeEan: {code: ''},
        quantity: 0,
    });
    const buyVariantDefault = reactive({
        productId: null,
        codeEan: {code: ''},
        quantity: 0,
    });

    const rules = {
        required: value => !!value || 'Field is required',
        positiveNumber: value => value > 0 || 'Quantity must be a positive number',
        codeEan: value => value.match(/^(\d{8}|\d{13})$/g) || 'Code EAN must have 8 or 13 digits',
        dimension: value => value.match(/^\d+(\.\d{1,2})?$/g) || 'Dimension must have forma 0000.00',
    };
    
    const store = useStore();
    const products = computed(() => {
        return store.state.mProductsList.list;
    });
    const productVariantsList = computed(() => {
        return store.state.mProductsList.productVariants;
    });

    const formTitle = computed(() => {
        return editedIndex.value === -1 ? 'New product' : 'Editing "' + store.state.mProductsList.list[editedIndex.value]?.name + '"';
    });

    watch(dialog, async(newValue) => {
        newValue || close();
    });
    watch(dialogBuy, async(newValue) => {
        newValue || closeBuy();
    });
    watch(dialogCreateVariant, async(newValue) => {
        newValue || closeCreateVariant();
    });

    function editItem (item) {
        editedIndex.value = store.state.mProductsList.list.indexOf(item);
        Object.assign(editedItem, item);
        dialog.value = true;
    };

    function createVariant (item) {
        editedIndex.value = store.state.mProductsList.list.indexOf(item);
        Object.assign(editedItem, item);
        Object.assign(newProductVariant, newProductVariantDefault);
        newProductVariant.productId = item.id;
        dialogCreateVariant.value = true;
    };

    function buyItem (item, codeEan) {
        editedIndex.value = store.state.mProductsList.list.indexOf(item);
        Object.assign(editedItem, item);
        Object.assign(buyVariant, buyVariantDefault);
        buyVariant.productId = item.id;
        buyVariant.codeEan.code = codeEan;
        dialogBuy.value = true;
    };

    function close () {
        dialog.value = false
        Object.assign(editedItem, defaultItem)
        editedIndex.value = -1;
    };

    function closeBuy () {
        dialogBuy.value = false;
        Object.assign(editedItem, defaultItem);
        Object.assign(buyVariant, buyVariantDefault);
        editedIndex.value = -1;
    };

    function closeCreateVariant () {
        dialogCreateVariant.value = false;
        Object.assign(newProductVariant, newProductVariantDefault);
        Object.assign(editedItem, defaultItem)
        editedIndex.value = -1;
    }

    function save () {
        apiError.value = null;
        store.dispatch('mProductsList/createProduct', editedItem);
        close();
    };

    function productVariants (productId) {
        while (productVariantsExpanded.length > 0) {
            productVariantsExpanded.pop();
        }
        
        apiError.value = null;
        store.dispatch('mProductsList/getProductVariants', {
            productId: productId,
            resultCallback: function (productId) {
                productVariantsExpanded.push(productId);
            }
        });
    }

    function createVariantSave () {
        apiError.value = null;
        store.dispatch('mProductsList/createProductVariant', {
            newProductVariant: toRaw(newProductVariant),
            errorCallback: function(error) {
                apiError.value = error?.response?.data?.error?.message ?? null;
            },
        });
        closeCreateVariant();
    };

    function buy () {
        store.dispatch('mProductsList/buyProductVariant', toRaw(buyVariant));
        closeBuy();
    };

    store.dispatch('mProductsList/fetchProducts');
</script>

<style scoped>
.productName:hover {
    cursor: pointer;
    color: #33f;
    text-decoration: underline;
}

.productVariant {
    background-color: #eaeaea;
}
</style>
