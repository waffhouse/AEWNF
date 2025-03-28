@props(['product'])

{{-- Use our global component with the default variant --}}
<x-product-item 
    :product="$product" 
    variant="default" 
    :itemKey="'product-card-'.$product['id']"
    :showDetails="true"
    :showQuantity="true"
    :showPrice="true"
/>