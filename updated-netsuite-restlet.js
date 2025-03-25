/**
 * @NApiVersion 2.1
 * @NScriptType RESTlet
 */
define(['N/search', 'N/log'], (search, log) => {

    function get(requestParams) {
        try {
            const savedSearchId = 'customsearchtotal_invoice_data';
            const invoiceSearch = search.load({ id: savedSearchId });

            let pageSize = parseInt(requestParams.pageSize) || 1000;
            if (pageSize > 1000) pageSize = 1000;

            // Optional specific date filter
            if (requestParams.date) {
                // Add a date filter to the search
                log.debug('Date Filter Applied', requestParams.date);
                invoiceSearch.filters.push(search.createFilter({
                    name: 'trandate',
                    operator: search.Operator.ON,
                    values: requestParams.date
                }));
            }

            // Run the paged search
            const pagedData = invoiceSearch.runPaged({ pageSize: pageSize });
            log.debug('Total Pages', pagedData.pageRanges.length);
            
            let transactionsMap = {};
            let processedCount = 0;
            
            // Process all pages to get complete dataset
            for (let i = 0; i < pagedData.pageRanges.length; i++) {
                // Output progress info for debugging
                log.audit('Processing Page', `${i + 1} of ${pagedData.pageRanges.length}`);
                
                const page = pagedData.fetch({ index: i });
                
                // Process each result on this page
                page.data.forEach(result => {
                    processedCount++;
                    const tranId = result.getValue({ name: 'tranid' });
                    const tranType = result.getText({ name: 'type' }); 
                    const tranDate = result.getValue({ name: 'trandate' });
                    const totalAmount = parseFloat(result.getValue({ name: 'total' })) || 0;

                    // From "Customer" join
                    const entityId = result.getValue({ name: 'entityid', join: 'customer' }) || '';
                    const customerName = result.getValue({ name: 'altname', join: 'customer' }) || '';

                    const quantity = parseFloat(result.getValue({ name: 'quantity' })) || 0;
                    const amount = parseFloat(result.getValue({ name: 'amount' })) || 0;
                    const itemDescription = result.getValue({ name: 'salesdescription', join: 'item' }) || '';
                    const itemSku = result.getValue({ name: 'itemid', join: 'item' }) || '';
                  
                    if (!transactionsMap[tranId]) {
                        transactionsMap[tranId] = {
                            tranId: tranId,
                            type: tranType,
                            date: tranDate,
                            entityId: entityId,
                            customerName: customerName,
                            totalAmount: totalAmount,
                            lines: []
                        };
                    }

                    transactionsMap[tranId].lines.push({
                        sku: itemSku,
                        item: itemDescription,
                        quantity: quantity,
                        amount: amount
                    });
                });
                
                // Governance considerations - don't run out of units
                if (i % 5 === 0) {
                    log.debug('Progress Update', `Processed ${processedCount} records across ${i+1} pages`);
                }
            }

            const transactionsArray = Object.values(transactionsMap);
            log.debug('Total Transactions', transactionsArray.length);

            const responseObj = {
                status: 'SUCCESS',
                totalPages: pagedData.pageRanges.length,
                totalTransactions: transactionsArray.length,
                totalRecordsProcessed: processedCount,
                data: transactionsArray
            };

            return JSON.stringify(responseObj);

        } catch (err) {
            log.error({ title: 'Error in RESTlet GET', details: err });
            return JSON.stringify({
                status: 'ERROR',
                message: err.message || err.toString()
            });
        }
    }

    function post(requestBody) {
        return JSON.stringify({ status: 'ERROR', message: 'POST not implemented' });
    }

    return {
        get: get,
        post: post
    };
});