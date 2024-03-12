import { defineStore } from 'pinia';
import axios from 'axios';
import type { Product, ProductVariant, CodeEan } from '@sf/interfaces/Product';

interface ProductState {
    list: Array<Product>,
    productVariants: Array<ProductVariant>
}

export const useProductsListStore = defineStore('productsList', {
    state: (): ProductState => ({
        list: [],
        productVariants: [],
    }),
    actions: {
        setList (products: Array<Product>) {
            while (this.list.length > 0) {
                this.list.pop();
            }

            this.list.push.apply(this.list, products);
        },
        addProduct (newProduct: Product) {
            this.list.push(newProduct);
        },
        setProductVariants (variants: Array<ProductVariant>) {
            while (this.productVariants.length > 0) {
                this.productVariants.pop();
            }

            this.productVariants.push.apply(this.productVariants, variants);
        },
        addProductVariant (variant: ProductVariant) {
            this.productVariants.push(variant);
        },
        fetchProducts (data: any|null = null) {
            const errorCallback: Function|null = data?.errorCallback;
            const getProductsPath = '/product';

            const storeThis = this;
            axios.get(getProductsPath)
                .then(function (response) {
                    storeThis.setList(response.data?.products ?? []);
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        createProduct (data: any|null = null) {
            const newProduct: Product = data?.newProduct;
            const errorCallback: Function|null = data?.errorCallback;
            const createProductPath = '/product';

            const storeThis = this;
            axios.post(createProductPath, newProduct)
                .then(function (response) {
                    if (null != response.data?.product) {
                        storeThis.addProduct(response.data?.product);
                    }
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        createProductVariant (data: any|null = null) {
            const newProductVariant: ProductVariant = data.newProductVariant;
            const successCallback: Function|null = data?.successCallback;
            const errorCallback: Function|null = data?.errorCallback;
            const createProductVariantPath = '/product/' + newProductVariant.productId + '/variant';

            const storeThis = this;
            axios.post(createProductVariantPath, newProductVariant)
                .then(function () {
                    storeThis.getProductVariants({
                        productId: newProductVariant.productId,
                        successCallback: successCallback,
                        errorCallback: errorCallback
                    });
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        getProductVariants (data: any|null = null) {
            const productId: number = data?.productId;
            const successCallback: Function|null = data?.successCallback;
            const errorCallback: Function|null = data?.errorCallback;
            const getProductVariantsPath = '/product/' + productId + '/variant';

            const storeThis = this;
            axios.get(getProductVariantsPath)
                .then(function (response) {
                    if (null != response.data?.variants) {
                        storeThis.setProductVariants(response.data?.variants);
                    }
                    if (null != successCallback) {
                        successCallback(productId);
                    }
                })
                .catch(function (error) {
                    if (null != errorCallback) {
                        errorCallback(error);
                    }
                });
        },
        buyProductVariant (data: any|null = null) {
            const productId: number = data?.productId;
            const codeEan: CodeEan = data?.codeEan;
            const quantity: number = data?.quantity;
            const errorCallback: Function|null = data?.errorCallback;
            const getProductVariantsPath = '/product/' + productId + '/variant/' + codeEan.code + '/buy/' + quantity;

            const storeThis = this;
            axios.post(getProductVariantsPath)
                .then(function (response) {
                    if (null != response.data?.variants) {
                        storeThis.setProductVariants(response.data?.variants);
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
});

