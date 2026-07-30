-- Sample India business services (replace UUIDs in production if needed)
INSERT INTO bs_services
(id, name, description, deleted, price, gst_percent, delivery_days, category, status, required_documents, date_entered, date_modified)
VALUES
(UUID(), 'GST Registration', 'New GSTIN registration for proprietorship/company/LLP', 0, 1499.00, 18.00, 7, 'Tax', 'active', 'PAN,AADHAAR,PHOTO,ADDRESS_PROOF,BANK', NOW(), NOW()),
(UUID(), 'Company Registration', 'Private Limited Company incorporation', 0, 6999.00, 18.00, 15, 'Incorporation', 'active', 'PAN,AADHAAR,PHOTO,ADDRESS_PROOF,DSC', NOW(), NOW()),
(UUID(), 'MSME Registration', 'Udyam / MSME registration', 0, 999.00, 18.00, 3, 'Registration', 'active', 'PAN,AADHAAR,BUSINESS_ADDRESS', NOW(), NOW()),
(UUID(), 'Trademark Registration', 'Trademark search + application filing', 0, 4999.00, 18.00, 20, 'IP', 'active', 'PAN,AADHAAR,LOGO,USER_AFFIDAVIT', NOW(), NOW()),
(UUID(), 'FSSAI License', 'Basic / State / Central FSSAI license', 0, 2499.00, 18.00, 10, 'License', 'active', 'PAN,AADHAAR,PHOTO,RENT_AGREEMENT,FOOD_SAFETY', NOW(), NOW());
