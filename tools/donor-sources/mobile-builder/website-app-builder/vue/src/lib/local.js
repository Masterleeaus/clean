import store from "@/store";

export const getLocalValue = (name, key) => {
    let value = store.state.language.values[name][key];
    return value === undefined ? "?" : value;
}