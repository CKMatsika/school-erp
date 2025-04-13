{{-- Alpine.js Logic with Console Logging --}}
<script>
    function invoiceItems(initialItems = [], taxRates = [], accounts = []) {
        console.log('[invoiceItems] Initializing with:', { initialItems, taxRates, accounts }); // Log initial data

        return {
            items: [], // Initialize empty, will be populated in init
            availableTaxRates: taxRates,
            availableAccounts: accounts,
            // Store summary totals reactively
            summary: {
                subtotal: 0,
                totalDiscount: 0,
                totalTax: 0,
                grandTotal: 0,
            },
            // Keep track of backend validation errors per item index
           itemErrors: {{ json_encode(optional(session('errors'))->getBag('default')->messages() ?? []) }}

            init() {
                console.log('[invoiceItems init] Starting initialization...');
                // Map initial data (from backend/old input) to the structure Alpine uses
                this.items = initialItems.map((item, index) => ({
                    id: item.id || null,
                    name: item.name || '',
                    description: item.description || '',
                    quantity: parseFloat(item.quantity) || 1,
                    price: parseFloat(item.price) || 0.00,
                    discount: parseFloat(item.discount) || 0.00,
                    tax_rate_id: item.tax_rate_id || '', // Use '' for "None" option value
                    account_id: item.account_id || '',
                    // For display only, calculated:
                    display_subtotal: 0,
                    display_tax: 0,
                    display_total: 0,
                    // Mark if this specific item index had a validation error
                    has_error: this.checkItemError(index)
                }));
                console.log('[invoiceItems init] Mapped items:', JSON.parse(JSON.stringify(this.items))); // Log mapped items

                 // Initial calculation for all existing items
                 this.items.forEach((_, index) => this.updateItemTotals(index, true)); // Pass flag to suppress summary recalc in loop
                 this.calculateSummaryTotals(); // Calculate initial summary ONCE after all items are processed
                 console.log('[invoiceItems init] Initial summary calculated:', JSON.parse(JSON.stringify(this.summary)));

                 // If creating a new invoice and no old input/items, add one blank line
                 const hasOldItemsInput = {{ json_encode(session()->hasOldInput('items')) }};
                 console.log('[invoiceItems init] Checking for new invoice state:', { initialItemsLength: initialItems.length, hasOldItemsInput });
                 if (initialItems.length === 0 && !hasOldItemsInput) {
                     console.log('[invoiceItems init] Adding initial blank item.');
                     this.addItem();
                 }
                 console.log('[invoiceItems init] Initialization complete.');
            },

            // Check if a specific item index has any validation errors
            checkItemError(index) {
                const prefix = `items.${index}.`;
                for (const key in this.itemErrors) {
                    if (key.startsWith(prefix)) {
                        return true;
                    }
                }
                return false;
            },
             hasItemError(index) { // Helper for template binding
                return this.items[index] ? this.items[index].has_error : false;
            },


            addItem() {
                console.log('[invoiceItems addItem] Adding new item row.');
                this.items.push({
                    id: null,
                    name: '',
                    description: '',
                    quantity: 1,
                    price: 0.00,
                    discount: 0.00,
                    tax_rate_id: '', // Default to "None"
                    account_id: '', // Default to "Select Account"
                    display_subtotal: 0,
                    display_tax: 0,
                    display_total: 0,
                    has_error: false // New items don't have errors initially
                });
                console.log('[invoiceItems addItem] Items after add:', JSON.parse(JSON.stringify(this.items)));
                 // Optional: Focus the name of the new row
                this.$nextTick(() => {
                   const lastInput = this.$refs.itemsContainer.querySelector('tr:last-child input[name$="[name]"]');
                   if(lastInput) {
                       console.log('[invoiceItems addItem] Focusing name input of new row.');
                       lastInput.focus();
                    }
                   this.calculateSummaryTotals(); // Recalculate summary when adding
                });
            },

            removeItem(index) {
                console.log(`[invoiceItems removeItem] Removing item at index: ${index}`);
                this.items.splice(index, 1);
                console.log('[invoiceItems removeItem] Items after remove:', JSON.parse(JSON.stringify(this.items)));
                this.calculateSummaryTotals(); // Recalculate summary when removing
            },

            // Calculates and updates display values for a single item
            // Added suppressSummaryRecalc flag for init loop efficiency
            updateItemTotals(index, suppressSummaryRecalc = false) {
                console.log(`[updateItemTotals] Triggered for index: ${index}`);
                if (!this.items[index]) {
                     console.warn(`[updateItemTotals] Item at index ${index} not found.`);
                     return;
                }

                const item = this.items[index];
                // Ensure values are treated as numbers, default to 0 if invalid
                const quantity = !isNaN(parseFloat(item.quantity)) ? parseFloat(item.quantity) : 0;
                const price = !isNaN(parseFloat(item.price)) ? parseFloat(item.price) : 0;
                const discount = !isNaN(parseFloat(item.discount)) ? parseFloat(item.discount) : 0;
                console.log(`[updateItemTotals index ${index}] Values:`, { quantity, price, discount, tax_rate_id: item.tax_rate_id });


                // 1. Calculate Item Subtotal (before tax, after discount)
                const subtotalBeforeDiscount = quantity * price;
                item.display_subtotal = Math.max(0, subtotalBeforeDiscount - discount); // Ensure not negative

                // 2. Calculate Tax
                let taxAmount = 0;
                if (item.tax_rate_id && item.tax_rate_id !== '') {
                    // Find the tax rate object from the available list
                    const taxRate = this.availableTaxRates.find(tr => tr.id == item.tax_rate_id); // Use == for loose comparison (string vs number)
                    if (taxRate) {
                        const rate = parseFloat(taxRate.rate) || 0;
                        taxAmount = (item.display_subtotal * rate) / 100;
                        console.log(`[updateItemTotals index ${index}] Tax calculated:`, { rate, taxAmount });
                    } else {
                         console.warn(`[updateItemTotals index ${index}] Tax Rate ID ${item.tax_rate_id} not found in availableTaxRates.`);
                    }
                } else {
                     console.log(`[updateItemTotals index ${index}] No Tax Rate ID selected.`);
                }
                item.display_tax = taxAmount;

                // 3. Calculate Item Total
                item.display_total = item.display_subtotal + item.display_tax;

                console.log(`[updateItemTotals index ${index}] Calculated display values:`, { subtotal: item.display_subtotal, tax: item.display_tax, total: item.display_total });

                // 4. Recalculate overall summary totals (unless suppressed during init)
                if (!suppressSummaryRecalc) {
                    this.calculateSummaryTotals();
                } else {
                    console.log(`[updateItemTotals index ${index}] Summary recalculation suppressed.`);
                }
            },

             // Calculate overall totals for the summary section
             calculateSummaryTotals() {
                 console.log('[calculateSummaryTotals] Starting summary calculation...');
                 let runningSubtotal = 0;       // Subtotal BEFORE discount
                 let runningTotalDiscount = 0;
                 let runningTotalTax = 0;
                 let runningGrandTotal = 0;     // Grand total AFTER discount and tax

                 this.items.forEach((item, index) => {
                     // Use the already calculated display values for consistency
                     const quantity = !isNaN(parseFloat(item.quantity)) ? parseFloat(item.quantity) : 0;
                     const price = !isNaN(parseFloat(item.price)) ? parseFloat(item.price) : 0;
                     const discount = !isNaN(parseFloat(item.discount)) ? parseFloat(item.discount) : 0;

                     runningSubtotal += quantity * price; // Base subtotal = sum of (qty * price)
                     runningTotalDiscount += discount;       // Sum of discount amounts entered
                     runningTotalTax += item.display_tax || 0; // Sum the calculated tax for each item
                     runningGrandTotal += item.display_total || 0; // Sum the calculated line total for each item
                 });

                 this.summary.subtotal = runningSubtotal;
                 this.summary.totalDiscount = runningTotalDiscount;
                 this.summary.totalTax = runningTotalTax;
                 this.summary.grandTotal = runningGrandTotal;

                 console.log('[calculateSummaryTotals] Summary calculated:', JSON.parse(JSON.stringify(this.summary)));
             },


            formatCurrency(amount) {
                 // Added check for null/undefined before isNaN
                 if (amount === null || typeof amount === 'undefined' || isNaN(amount)) amount = 0;
                 return (amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
</script>