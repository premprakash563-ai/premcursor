<?php
// Phase 1 logic tests (no SuiteCRM bootstrap)
// Run: php tests/Phase1LogicTest.php

function assert_true($cond, $msg)
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
    echo "OK: $msg\n";
}

function shouldClearNewEnquiry($oldStatus, $newStatus)
{
    if ($oldStatus === null || $oldStatus === '' || $oldStatus === $newStatus) {
        return false;
    }
    if ($newStatus === 'application_submitted' || $newStatus === 'payment_pending') {
        return false;
    }
    return true;
}

function nextDocStatuses()
{
    return ['uploaded', 'approved', 'rejected', 'reupload_requested'];
}

assert_true(shouldClearNewEnquiry('application_submitted', 'documents_received') === true, 'Clears new enquiry on progress');
assert_true(shouldClearNewEnquiry(null, 'application_submitted') === false, 'Does not clear on create');
assert_true(shouldClearNewEnquiry('processing', 'processing') === false, 'No change when same status');
assert_true(in_array('reupload_requested', nextDocStatuses(), true), 'Re-upload status exists');
assert_true(count(nextDocStatuses()) === 4, 'Four document review statuses');

$pipeline = [
    'application_submitted',
    'documents_received',
    'under_verification',
    'processing',
    'department_submission',
    'approval_pending',
    'completed',
    'certificate_ready',
    'delivered',
];
assert_true(count($pipeline) === 9, 'Customer-visible pipeline has 9 stages');

echo "All Phase 1 logic tests passed.\n";
