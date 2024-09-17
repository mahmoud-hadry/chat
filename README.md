Documentation
chat
﻿

POST
listOfChats
http://127.0.0.1:8000/api/chat/chatsList
﻿

Request Headers
Accept-Language
en
Authorization
bea
Body
raw (json)
json
{
    "patientId":1
    }
POST
messagesHistory
http://127.0.0.1:8000/api/chat/messagesHistory
﻿

Request Headers
Accept-Language
en
Body
raw (json)
json
{
    "patientId":20044,
    "doctorId":24105
    }
POST
availableDoctors
http://127.0.0.1:8000/api/chat/available
﻿

Request Headers
Cache-Control
no-cache
Postman-Token
<calculated when request is sent>
Content-Type
application/json
Content-Length
<calculated when request is sent>
Host
<calculated when request is sent>
User-Agent
PostmanRuntime/7.39.1
Accept
*/*
Accept-Encoding
gzip, deflate, br
Connection
keep-alive
Accept-Language
en
Body
raw (json)
json
{
    "patientId":1
}
POST
sendmessage
http://127.0.0.1:8000/api/chat/send
﻿

Request Headers
Content-Type
multipart/form-data
Accept
application/json
Body
form-data
patientId
1
from_id
2
to_id
1
message
hi i'm patient
