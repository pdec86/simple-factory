import moduleProductsList from "./modules/productsList";
import { createStore } from 'vuex';

const store = createStore({
    devtools: false,
    modules: {
        mProductsList: {...moduleProductsList, namespaced: true}
    }
});

export { store };
