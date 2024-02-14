import axios from 'axios';

const moduleProductsList = {
    state: () => ({
        list: [],
        productVariants: [],
    }),
    mutations: {
        setList (state, products) {
            while (state.list.length > 0) {
                state.list.pop();
            }

            state.list.push.apply(state.list, products);
        },
        addProduct (state, newProduct) {
            state.list.push(newProduct);
        },
        setProductVariants (state, variants) {
            while (state.productVariants.length > 0) {
                state.productVariants.pop();
            }

            state.productVariants.push.apply(state.productVariants, variants);
        },
        addProductVariant (state, variant) {
            state.productVariants.push(variant);
        },
    },
    actions: {
        fetchProducts (context, errorCallback = null) {
            const getProductsPath = '/product';

            axios.get(getProductsPath)
                .then(function (response) {
                    context.commit('setList', response.data?.products ?? []);
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        createProduct (context, newProduct) {
            const createProductPath = '/product';

            axios.post(createProductPath, newProduct)
                .then(function (response) {
                    if (null != response.data?.product) {
                        context.commit('addProduct', response.data?.product);
                    }
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        createProductVariant (context, data) {
            const newProductVariant = data.newProductVariant;
            const errorCallback = data.errorCallback;
            const createProductVariantPath = '/product/' + newProductVariant.productId + '/variant';

            axios.post(createProductVariantPath, newProductVariant)
                .then(function () {
                    context.dispatch('getProductVariants', newProductVariant.productId);
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        getProductVariants (context, data, errorCallback = null) {
            const getProductVariantsPath = '/product/' + data.productId + '/variant';

            axios.get(getProductVariantsPath)
                .then(function (response) {
                    if (null != response.data?.variants) {
                        context.commit('setProductVariants', response.data?.variants);
                    }
                    if (null != data.resultCallback) {
                        data.resultCallback(data.productId);
                    }
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        buyProductVariant (context, data) {
            const getProductVariantsPath = '/product/' + data.productId + '/variant/' + data.codeEan.code + '/buy/' + data.quantity;

            axios.post(getProductVariantsPath)
                .then(function (response) {
                    if (null != response.data?.variants) {
                        context.commit('setProductVariants', response.data?.variants);
                    }
                    if (null != data.resultCallback) {
                        data.resultCallback(data.productId);
                    }
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        }
    },
    getters: {
    }
}

export default moduleProductsList;
