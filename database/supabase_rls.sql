-- ============================================
-- RUN SCRIPT NÀY TRÊN SUPABASE SQL EDITOR
-- Settings -> SQL Editor -> New Query -> Paste -> Run
-- ============================================

-- Bật Row Level Security (Bảo mật theo từng user)
ALTER TABLE transactions ENABLE ROW LEVEL SECURITY;

-- Policy: Chỉ xem được giao dịch của chính mình
CREATE POLICY "transactions_select_own" 
ON transactions FOR SELECT 
USING (auth.uid() = user_id);

-- Policy: Chỉ thêm giao dịch cho chính mình
CREATE POLICY "transactions_insert_own" 
ON transactions FOR INSERT 
WITH CHECK (auth.uid() = user_id);

-- Policy: Chỉ xóa giao dịch của chính mình
CREATE POLICY "transactions_delete_own" 
ON transactions FOR DELETE 
USING (auth.uid() = user_id);

-- Policy: Chỉ cập nhật giao dịch của chính mình
CREATE POLICY "transactions_update_own" 
ON transactions FOR UPDATE 
USING (auth.uid() = user_id);

-- Bật Realtime cho bảng transactions (cập nhật live trên app)
-- Vào Supabase Dashboard -> Database -> Replication -> Bật "transactions"
-- Hoặc chạy lệnh dưới:
ALTER PUBLICATION supabase_realtime ADD TABLE transactions;
