# CariKostKita — Database Design

## Main Tables

### users
- id
- name
- email
- password
- role
- phone
- avatar
- email_verified_at
- timestamps

### profiles
- id
- user_id
- bio
- address
- city
- district
- timestamps

### kosts
- id
- owner_id
- name
- description
- address
- province
- city
- district
- village
- latitude
- longitude
- price_min
- price_max
- gender_type
- facilities
- status
- verification_status
- timestamps

### kost_images
- id
- kost_id
- file_path
- url
- is_primary
- timestamps

### rooms
- id
- kost_id
- name
- room_type
- price
- size
- facilities
- total_rooms
- available_rooms
- status
- timestamps

### favorites
- id
- user_id
- kost_id
- timestamps

Unique constraint:
`user_id + kost_id`

### chats
- id
- user_id
- owner_id
- kost_id
- timestamps

### messages
- id
- chat_id
- sender_id
- message
- is_read
- timestamps

### reports
- id
- reporter_id
- kost_id
- reason
- description
- status
- timestamps

### notifications
- id
- user_id
- type
- title
- message
- read_at
- timestamps

## Relationships
```text
User 1---* Kost
Kost 1---* KostImage
Kost 1---* Room
User *---* Kost through Favorite
Chat 1---* Message
Kost 1---* Chat
```

## Rules
- Foreign keys wajib digunakan.
- Index kolom yang sering dicari/filter.
- Unique constraint untuk favorite.
- Jangan menghapus data penting tanpa strategi yang jelas.
- Gunakan timestamps.
