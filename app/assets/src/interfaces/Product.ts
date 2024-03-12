export interface Product {
    id: number|null,
    name: string,
    description: string,
}

export interface ProductVariant {
    productId: number|null,
    name: string,
    codeEan: CodeEan,
    length: string,
    width: string,
    height: string
}

export interface CodeEan {
    code: string
}

export interface BuyProductVariant {
    productId: number | null,
    codeEan: CodeEan,
    quantity: number,
}