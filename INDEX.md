## SQL INDEX

# สร้าง INDEX สำหรับตาราง content

````
CREATE INDEX idx_content_filter ON content (active, type_id, status, is_hidden, updated_at);```
````
