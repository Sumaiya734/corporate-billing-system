<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: modalSlideIn 0.2s ease-out;">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545;"></i>
        </div>
        <h4 style="text-align: center; margin-bottom: 15px; color: #2c3e50;">Confirm Deletion</h4>
        <p id="deleteModalMessage" style="text-align: center; color: #7f8c8d; margin-bottom: 25px;"></p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button id="cancelDeleteBtn" class="btn btn-secondary" style="min-width: 120px;">
                <i class="fas fa-times me-1"></i>Cancel
            </button>
            <button id="confirmDeleteBtn" class="btn btn-danger" style="min-width: 120px;">
                <i class="fas fa-trash me-1"></i>Delete
            </button>
        </div>
    </div>
</div>

<style>
    /* Modal animation */
    @keyframes modalSlideIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>

<script>
    // Delete Modal Functions
    (function() {
        // Prevent multiple initializations
        if (window.deleteModalInitialized) return;
        window.deleteModalInitialized = true;
        
        const deleteModal = document.getElementById('deleteModal');
        const deleteModalMessage = document.getElementById('deleteModalMessage');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        
        let pendingDeleteAction = null;
        let pendingDeleteRow = null;
        let pendingDeleteCallback = null;
        let isProcessing = false; // Add processing flag
        
        window.showDeleteModal = function(message, action, row, callback) {
            deleteModalMessage.innerHTML = message;
            deleteModal.style.display = 'flex';
            pendingDeleteAction = action;
            pendingDeleteRow = row;
            pendingDeleteCallback = callback;
            
            // Focus on cancel button for accessibility
            setTimeout(() => cancelDeleteBtn.focus(), 100);
        };
        
        window.hideDeleteModal = function() {
            deleteModal.style.display = 'none';
            pendingDeleteAction = null;
            pendingDeleteRow = null;
            pendingDeleteCallback = null;
        };
        
        function executeDelete() {
            // Prevent multiple clicks
            if (isProcessing || !pendingDeleteAction) return;
            
            isProcessing = true;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Disable buttons during request
            confirmDeleteBtn.disabled = true;
            cancelDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
            
            fetch(pendingDeleteAction, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: '_method=DELETE'
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    if (response.ok) {
                        return { success: true, message: 'Deleted successfully' };
                    } else {
                        throw new Error('Server returned non-JSON response');
                    }
                }
            })
            .then(data => {
                hideDeleteModal();
                
                if (data.success) {
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('Success', data.message || 'Deleted successfully', 'success');
                    } else {
                        alert(data.message || 'Deleted successfully');
                    }
                    
                    // Remove row with animation
                    if (pendingDeleteRow) {
                        pendingDeleteRow.style.transition = 'opacity 0.3s';
                        pendingDeleteRow.style.opacity = '0';
                        setTimeout(() => {
                            pendingDeleteRow.remove();
                            // Call callback if provided
                            if (pendingDeleteCallback) pendingDeleteCallback();
                        }, 300);
                    } else if (pendingDeleteCallback) {
                        pendingDeleteCallback();
                    } else {
                        // Reload page if no row or callback
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Error', data.message || 'Failed to delete', 'error');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete'));
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideDeleteModal();
                if (typeof showToast === 'function') {
                    showToast('Error', 'An error occurred while deleting', 'error');
                } else {
                    alert('Error: An error occurred while deleting');
                }
            })
            .finally(() => {
                // Re-enable buttons
                confirmDeleteBtn.disabled = false;
                cancelDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
                isProcessing = false; // Reset processing flag
            });
        }
        
        // Modal event listeners with debounce
        confirmDeleteBtn.addEventListener('click', function() {
            if (!isProcessing) {
                isProcessing = true;
                setTimeout(() => isProcessing = false, 300);
                executeDelete();
            }
        });
        
        cancelDeleteBtn.addEventListener('click', function() {
            if (!isProcessing) {
                isProcessing = true;
                setTimeout(() => isProcessing = false, 300);
                hideDeleteModal();
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteModal.style.display === 'flex') {
                if (!isProcessing) {
                    isProcessing = true;
                    setTimeout(() => isProcessing = false, 300);
                    hideDeleteModal();
                }
            }
        });
        
        // Close modal on backdrop click
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                if (!isProcessing) {
                    isProcessing = true;
                    setTimeout(() => isProcessing = false, 300);
                    hideDeleteModal();
                }
            }
        });
    })();
</script>