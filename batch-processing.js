jQuery(document).ready(function($) {
    $('#create-users-button').on('click', function(e) {
        e.preventDefault();
        
        var batchNumber = 0;

        function updateBatchStatus(batchNumber) {
            $('#batch-status').text("Processing batch number: " + batchNumber);
        }

        function processBatch() {
            updateBatchStatus(batchNumber);
            var startTime = performance.now();
            
            $.post(batchProcessingData.ajax_url, {
                action: 'create_users_batch',
                nonce: batchProcessingData.nonce,
                batch_number: batchNumber
            }, function(response) {
                var endTime = performance.now();

                if (response.success) {
                    if (response.data.has_more_batches) {
                        batchNumber = response.data.batch_number;
                        processBatch();
                    } else {
                        window.location.href = batchProcessingData.redirect_url;
                    }
                } else {
                    console.log("Batch " + batchNumber + " failed");
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.log("Batch " + batchNumber + " request failed: " + textStatus + ", " + errorThrown);
            });
        }

        processBatch();
    });
});
