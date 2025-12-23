-- Indexes recommended for production
CREATE INDEX IF NOT EXISTS idx_invoices_date ON invoices (date);
CREATE INDEX IF NOT EXISTS idx_invoices_status ON invoices (status);
CREATE INDEX IF NOT EXISTS idx_invoice_items_invoice_id ON invoice_items (invoice_id);
CREATE INDEX IF NOT EXISTS idx_invoice_items_product_id ON invoice_items (product_id);
CREATE INDEX IF NOT EXISTS idx_inventory_movements_created_at ON inventory_movements (created_at);
CREATE INDEX IF NOT EXISTS idx_inventory_movements_type ON inventory_movements (type);
CREATE INDEX IF NOT EXISTS idx_products_name ON products (name);

-- Optional compound index if topProducts query benefits (status + date or status + invoice_id)
-- CREATE INDEX IF NOT EXISTS idx_invoices_status_date ON invoices (status, date);

-- Update planner statistics (run after creating indexes)
VACUUM ANALYZE invoices;
VACUUM ANALYZE invoice_items;
VACUUM ANALYZE inventory_movements;
VACUUM ANALYZE products;
