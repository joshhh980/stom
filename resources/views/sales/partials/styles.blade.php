<style>
    /* ---------- Item picker grid ---------- */
    #item_picker {
        max-height: 360px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px;
        background: #fafafa;
    }
    #item_grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    .item-card {
        position: relative;
        background: #fff;
        border: 2px solid #e2e6ea;
        border-radius: 6px;
        padding: 10px 10px 8px;
        cursor: pointer;
        transition: box-shadow .12s ease, transform .12s ease, border-color .12s ease;
        user-select: none;
    }
    .item-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
        transform: translateY(-1px);
        border-color: #b8c2cc;
    }
    .item-card:active { transform: translateY(0); }
    .item-card.in-cart { border-color: #007bff; background: #f0f7ff; }
    .item-card.pulse { animation: cardPulse .3s ease; }
    
    @keyframes cardPulse {
        0% { transform: scale(1); }
        40% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .item-card .name {
        font-weight: 600;
        font-size: 13px;
        display: block;
        margin-bottom: 4px;
        line-height: 1.25;
    }
    .item-card .price {
        font-size: 13px;
        color: #007bff;
        font-weight: 600;
    }
    .item-card .stock-tag {
        display: inline-block;
        margin-top: 6px;
        font-size: 11px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 8px;
    }
    .item-card .stock-tag.ok { background: #d4edda; color: #155724; }
    .item-card .stock-tag.low { background: #fff3cd; color: #856404; }
    
    .item-card .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #007bff;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        line-height: 20px;
        border-radius: 10px;
        text-align: center;
        padding: 0 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,.3);
    }
    #item_search_empty {
        text-align: center;
        color: #6c757d;
        padding: 24px;
        font-style: italic;
        display: none;
    }

    /* ---------- Cart table ---------- */
    .editable-cell {
        cursor: pointer;
        border-bottom: 1px dashed #adb5bd;
    }
    .editable-cell:hover { background: #f0f7ff; }
    .editable-cell .cell-input {
        width: 90px;
        display: inline-block;
        padding: 2px 6px;
        height: 28px;
    }
    #list tbody tr.row-warning td { background-color: #fff8e6 !important; }
    #list tbody tr .item-name { font-weight: 600; }
    #list tbody tr .item-desc { color: #6c757d; font-size: 12px; }
    #empty-row td {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-style: italic;
    }
    tfoot .grand-total { font-size: 16px; font-weight: 700; }
</style>
