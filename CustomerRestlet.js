/**
 * @NApiVersion 2.x
 * @NScriptType Restlet
 */
define(['N/search'], function(search) {

    /**
     * Function called upon receiving a GET request.
     * It returns customer data along with shipping address, county, home state, license type, and license number.
     *
     * @param {Object} requestParams - Request parameters sent to the RESTlet.
     * @returns {string} - A JSON string representing customer data.
     */
    function doGet(requestParams) {
        var response;
        
        // Define a common columns array for the search
        var baseColumns = [
            'internalid',
            'entityid',
            'companyname',
            'email',
            'shipaddress',
            'custentity_county',          // County field
            'custentity_dsd_homestate',    // Home state field
            'custentity_license_type',     // License Type field (no join)
            'custentity_tlicenseno',       // License Number field
            'phone',
            'priceLevel',
            'terms'
        ];

        // If a customer ID is provided, return details for that specific customer
        if (requestParams && requestParams.id) {
            var customerId = requestParams.id;
            var customerSearch = search.create({
                type: search.Type.CUSTOMER,
                filters: [
                    ['internalid', 'is', customerId],
                    'and',
                    ['isinactive', 'is', 'F']
                ],
                columns: baseColumns
            });
            var searchResult = customerSearch.run().getRange({ start: 0, end: 1 });
            if (searchResult && searchResult.length > 0) {
                var result = searchResult[0];
                response = {
                    id: result.getValue('internalid'),
                    entityId: result.getValue('entityid'),
                    companyName: result.getValue('companyname'),
                    email: result.getValue('email'),
                    shippingAddress: result.getValue('shipaddress'),
                    county: result.getText('custentity_county'),
                    homeState: result.getText('custentity_dsd_homestate'),
                    licenseType: result.getText('custentity_license_type'),
                    tLicenseNo: result.getValue('custentity_tlicenseno'),
                    phone: result.getValue('phone'),
                    priceLevel: result.getText('priceLevel'),
                    terms: result.getText('terms')
                };
            } else {
                response = { error: 'Customer not found' };
            }
        }
        // If no customer ID is provided, return a list of active customers using paged search
        else {
            var customers = [];
            var customerSearch = search.create({
                type: search.Type.CUSTOMER,
                filters: [
                    ['isinactive', 'is', 'F']
                ],
                columns: baseColumns
            });
            
            // Use paged search to handle more than 1000 results
            var pagedData = customerSearch.runPaged({
                pageSize: 1000
            });
            pagedData.pageRanges.forEach(function(pageRange) {
                var page = pagedData.fetch({ index: pageRange.index });
                page.data.forEach(function(result) {
                    customers.push({
                        id: result.getValue('internalid'),
                        entityId: result.getValue('entityid'),
                        companyName: result.getValue('companyname'),
                        email: result.getValue('email'),
                        shippingAddress: result.getValue('shipaddress'),
                        county: result.getText('custentity_county'),
                        homeState: result.getText('custentity_dsd_homestate'),
                        licenseType: result.getText('custentity_license_type'),
                        tLicenseNo: result.getValue('custentity_tlicenseno'),
                        phone: result.getValue('phone'),
                        priceLevel: result.getText('priceLevel'),
                        terms: result.getText('terms')
                    });
                });
            });
            response = customers;
        }
        // Convert the response to a JSON string before returning
        return JSON.stringify(response);
    }

    return {
        get: doGet
    };
});
