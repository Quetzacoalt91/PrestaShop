module.exports = {
  OrderPage:{
    orders_subtab: '#subtab-AdminParentOrders',
    form: '#form-order',
    view_order_button: '//*[@id="form-order"]//tr[%NUMBER]//div[contains(@class,"pull-right")]//a',
    order_state_select: '//*[@id="id_order_state"]',
    update_status_button: '//*[@id="status"]/form//button[@name="submitState"]',
    order_quantity: '//*[@id="orderProducts"]//tr[1]//span[@class="product_quantity_show badge"]',
    order_status: '//*[@id="status"]//table[contains(@class,"history-status")]//tr[1]/td[2]',
    shipping_cost: '//*[@id="shipping_table"]//tr[1]//td[5]/span',
    message_order: '//*[@id="content"]//div[@class="message-body"]//p[@class="message-item-text"]',
    product_Url: '//*[@id="orderProducts"]//tr[1]/td[2]/a',
    edit_product_button: '//*[@id="orderProducts"]//button[contains(@class,"edit_product_change_link")]',
    product_basic_price: '//*[@id="orderProducts"]//tr[1]//input[contains(@class,"edit_product_price_tax_excl")]',
    customer_name: '//*[@id="content"]//div[@class="message-body"]//h4[@class="message-item-heading"]',
    order_submenu: '//*[@id="subtab-AdminOrders"]/a',
    document_submenu:'//*[@id="tabOrder"]/li[2]/a',
    download_invoice_button:'//*[@id="documents_table"]//tr[1]/td[3]/a',
    download_delivery_button:'//*[@id="documents_table"]//tr[3]/td[3]/a',
    product_name: '//*[@id="orderProducts"]//tr[1]//span[@class="productName"]',
    total_price: '//*[@id="total_products"]/td[contains(@class,"amount")]',
    shipping_method: '//*[@id="shipping_table"]//tr/td[3]',
    search_by_reference_input: '//*[@id="form-order"]//table[contains(@class,"order")]//input[@name="orderFilter_reference"]',
    search_order_button: '//*[@id="submitFilterButtonorder"]'
  },

  CreateOrder:{
    new_order_button: '//*[@id="page-header-desc-order-new_order"]',
    customer_search_input: '//*[@id="customer"]',
    choose_customer_button: '//*[@id="customers"]/div[1]/div/div[2]/button',
    product_search_input: '//*[@id="product"]',
    quantity_input: '//*[@id="qty"]',
    add_to_cart_button: '//*[@id="submitAddProduct"]',
    order_message_textarea: '//*[@id="order_message"]',
    delivery_option: '//*[@id="delivery_option"]',
    payment: '//*[@id="payment_module_name"]',
    total_shipping:'//*[@id="total_shipping"]',
    create_order_button: '//*[@id="summary_part"]//button[@name="submitAddOrder"]',
    product_combination: '//*[@id="ipa_2"]',
    basic_price_value: '//*[@id="customer_cart"]//tr[1]//input[@class="product_unit_price"]',
    product_select:'//*[@id="id_product"]'
  }
};