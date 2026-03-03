# 📱 Chat Architecture Plan - MySQL + FCM (Perceived Real-Time)

> **Created:** 28 Februari 2026  
> **Status:** Approved for Implementation  
> **Architecture:** MySQL Primary + FCM for Instant Notifications

---

## 🎯 Executive Summary

**Problem:** Real-time chat dengan polling MySQL memiliki delay 5-10 detik.

**Solution:** Gunakan FCM push notification untuk memberikan "perceived real-time" experience tanpa biaya infrastruktur real-time yang mahal.

**Result:** User merasa chat berjalan instant, padahal backend menggunakan polling MySQL yang simple dan cost-effective.

---

## 🏗️ Architecture Overview

### **Hybrid Approach: MySQL + FCM**

```
┌─────────────────┐
│   Sender        │
│  (Kurir/Merchant)│
└────────┬────────┘
         │
         │ 1. POST /api/chat/send (instant)
         ▼
┌─────────────────┐
│   Laravel       │
│    MySQL        │──────┐
└────────┬────────┘      │
         │                │ 3. Trigger FCM
         │ 2. Save to DB  │    (instant)
         ▼                ▼
┌─────────────────┐  ┌─────────────────┐
│  MySQL Database │  │  Firebase FCM   │
│  (Source of     │  │  (Push Notif)   │
│   Truth)        │  │                 │
└─────────────────┘  └────────┬────────┘
                              │
                              │ 4. Push Notification
                              │    (< 1 sec)
                              ▼
                        ┌─────────────────┐
                        │   Receiver      │
                        │   (Customer)    │
                        └────────┬────────┘
                                 │
                                 │ 5. User sees notification
                                 │ 6. User taps notification
                                 │ 7. App fetches data
                                 ▼
                        ┌─────────────────┐
                        │  GET /api/chats │
                        │  (Data ready)   │
                        └─────────────────┘
```

---

## ⏱️ Timeline & User Perception

| Step | Action | Duration | User Perception |
|------|--------|----------|-----------------|
| 1 | Sender mengirim pesan | < 1 sec | ✅ "Terkirim" |
| 2 | Backend save ke MySQL | < 1 sec | - |
| 3 | Backend trigger FCM | < 1 sec | - |
| 4 | FCM push ke device receiver | < 1 sec | 🔔 "Ting!" notification |
| 5 | User membaca notification preview | 2-5 sec | 👀 Reading preview |
| 6 | User tap notification & app opens | 1-2 sec | 📱 Opening app |
| 7 | App fetches data dari API | < 1 sec | 💬 Message appears |

**Total User-Perceived Time:** 5-10 detik  
**Actual Message Delivery:** < 1 detik (via FCM)  
**Message Content Load:** Saat user tap notification

---

## 📋 Implementation Tasks

### **Phase 1: Backend FCM Integration** (2 jam)

#### Task 1.1: Update ChatController.php
**File:** `app/Http/Controllers/API/ChatController.php`

**Add Method:**
```php
private function sendChatNotification(Chat $chat, $sender, string $message): void
{
    // Determine recipient (the other person in chat)
    $recipientId = ($chat->user_id == $sender->id) 
        ? $chat->recipient_id 
        : $chat->user_id;
    
    $recipient = User::find($recipientId);
    
    if ($recipient) {
        // Get active FCM tokens
        $tokens = $recipient->fcmTokens()
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
        
        if (!empty($tokens)) {
            $this->firebaseService->sendToUser(
                $tokens,
                [
                    'type' => 'CHAT_MESSAGE',
                    'chat_id' => $chat->id,
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                ],
                'Pesan Baru',
                "{$sender->name}: " . substr($message, 0, 50)
            );
        }
    }
}
```

**Update Methods:**
- `sendMessage()` - Call FCM notification after save
- `initiate()` - Call FCM notification for first message

---

#### Task 1.2: Test Backend FCM
**Test Scenarios:**
1. ✅ Send message → FCM sent to recipient
2. ✅ User offline → FCM queued by Firebase
3. ✅ User online → FCM received instantly
4. ✅ Multiple devices → FCM sent to all active tokens

---

### **Phase 2: Flutter FCM Handler** (2 jam)

#### Task 2.1: Update FCM Token Service
**File:** `mobile/customer/lib/app/services/fcm_token_service.dart`

**Add Handler:**
```dart
void _handleMessage(RemoteMessage message) {
  final data = message.data;
  
  if (data['type'] == 'CHAT_MESSAGE') {
    // Show local notification immediately
    _showChatNotification(
      title: data['sender_name'] ?? 'Pesan Baru',
      body: message.notification?.body ?? 'Anda memiliki pesan baru',
      chatId: data['chat_id'],
    );
    
    // Refresh chat list in background (if controller exists)
    if (Get.isRegistered<ChatListController>()) {
      Get.find<ChatListController>().fetchChats();
    }
  }
}

void _showChatNotification({
  required String title,
  required String body,
  String? chatId,
}) async {
  final flutterLocalNotificationsPlugin = 
      Get.find<FlutterLocalNotificationsPlugin>();
  
  await flutterLocalNotificationsPlugin.show(
    DateTime.now().millisecondsSinceEpoch.remainder(100000),
    title,
    body,
    NotificationDetails(
      android: AndroidNotificationDetails(
        'chat_channel',
        'Chat Messages',
        channelDescription: 'Notifikasi pesan chat',
        importance: Importance.high,
        priority: Priority.high,
        icon: '@mipmap/ic_notification',
      ),
    ),
    payload: json.encode({'chatId': chatId}),
  );
}
```

---

#### Task 2.2: Add Auto-Refresh on App Resume
**File:** `mobile/customer/lib/app/modules/chat/views/chat_list_page.dart`

**Change to StatefulWidget:**
```dart
class ChatListPage extends StatefulWidget {
  const ChatListPage({Key? key}) : super(key: key);

  @override
  State<ChatListPage> createState() => _ChatListPageState();
}

class _ChatListPageState extends State<ChatListPage> 
    with WidgetsBindingObserver {
  final controller = Get.put(ChatListController());

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    controller.fetchChats(); // Immediate fetch on open
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      // Refresh when app comes to foreground
      controller.fetchChats();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // ... existing build code ...
  }
}
```

---

### **Phase 3: Optimize Polling Strategy** (1 jam)

#### Task 3.1: Chat List Polling (30 seconds)
**File:** `mobile/customer/lib/app/modules/chat/controllers/chat_list_controller.dart`

```dart
@override
void onInit() {
  super.onInit();
  fetchChats(); // Immediate fetch
  
  // Polling setiap 30 detik untuk chat list
  _refreshSubscription = Stream.periodic(
    Duration(seconds: 30)
  ).listen((_) => fetchChats());
}
```

**Rationale:**
- Chat list tidak perlu real-time instant
- User biasanya buka chat list dari notification
- 30 detik cukup untuk update unread count
- Reduce server load

---

#### Task 3.2: Chat Detail Polling (5 seconds)
**File:** `mobile/customer/lib/app/modules/chat/controllers/chat_controller.dart`

```dart
@override
void onInit() {
  super.onInit();
  
  if (chatId != null) {
    fetchMessages(); // Immediate fetch
    
    // Polling setiap 5 detik untuk chat detail
    _messagesSubscription = Stream.periodic(
      Duration(seconds: 5)
    ).listen((_) => fetchMessages());
  }
}
```

**Rationale:**
- User actively viewing conversation
- 5 detik acceptable untuk back-and-forth chat
- More frequent = better UX for active chat
- Still much cheaper than WebSocket/Firestore

---

### **Phase 4: Testing & QA** (2 jam)

#### Test Scenario 1: Single Message Flow
```
1. Kurir send message → ✅
2. Backend save to MySQL → ✅
3. Backend trigger FCM → ✅
4. Customer receives notification (< 1 sec) → ✅
5. Customer taps notification → ✅
6. App opens & fetches data → ✅
7. Message appears → ✅
```

#### Test Scenario 2: Multiple Messages
```
1. Send 3 messages in 10 seconds → ✅
2. Receiver gets 3 notifications (or batched) → ✅
3. Open app → All 3 messages appear → ✅
4. Unread count updates correctly → ✅
```

#### Test Scenario 3: Background vs Foreground
```
Background:
- App in background → FCM shows system notification → ✅
- Tap notification → App opens to chat → ✅

Foreground:
- App in foreground → Show in-app notification/banner → ✅
- Chat auto-refreshes → ✅
```

#### Test Scenario 4: Offline Handling
```
1. Sender offline → Message queued, retry → ✅
2. Receiver offline → FCM queued by Firebase → ✅
3. Receiver comes online → Receives notification → ✅
4. Opens app → Messages sync → ✅
```

---

## 📁 Files to Create/Modify

### Backend (Laravel)
| File | Action | Status |
|------|--------|--------|
| `app/Http/Controllers/API/ChatController.php` | Add FCM trigger method | ⬜ |
| `app/Services/FirebaseService.php` | Already has sendToUser() | ✅ |

### Frontend (Flutter)
| File | Action | Status |
|------|--------|--------|
| `mobile/customer/lib/app/services/fcm_token_service.dart` | Add chat message handler | ⬜ |
| `mobile/customer/lib/app/modules/chat/views/chat_list_page.dart` | Add auto-refresh on resume | ⬜ |
| `mobile/customer/lib/app/modules/chat/controllers/chat_list_controller.dart` | Optimize polling (30s) | ⬜ |
| `mobile/customer/lib/app/modules/chat/controllers/chat_controller.dart` | Optimize polling (5s) | ⬜ |

---

## 🎯 Success Metrics

### Performance Metrics
- **FCM Delivery Time:** < 1 second (Firebase SLA)
- **App Cold Start:** < 3 seconds
- **Message Load Time:** < 1 second after app opens
- **Polling Frequency:** 30s (list), 5s (detail)

### User Experience Metrics
- **Notification to Open Time:** 2-5 seconds (user behavior)
- **Perceived Real-Time:** ✅ Achieved via FCM
- **Server Load:** Minimal (polling, not WebSocket)

### Cost Metrics
- **Firebase Cost:** Free tier (FCM is free!)
- **Server Cost:** Same as before (no upgrade needed)
- **Development Time:** 6-7 jam total

---

## 🚀 Deployment Checklist

### Pre-Deployment
- [ ] Backend FCM integration tested locally
- [ ] Flutter FCM handler tested
- [ ] Polling intervals configured
- [ ] Auto-refresh on resume working

### Deployment
- [ ] Deploy backend changes
- [ ] Test FCM in production
- [ ] Monitor Firebase Console for delivery rates

### Post-Deployment
- [ ] Monitor user engagement with chat
- [ ] Track notification open rates
- [ ] Collect user feedback on chat responsiveness

---

## 📊 Monitoring & Analytics

### Firebase Console
- **FCM Delivery Rate:** Target > 95%
- **Notification Open Rate:** Track % users who tap chat notifications
- **Device Coverage:** % users with valid FCM tokens

### Backend Logs
- **FCM Trigger Success Rate:** Log success/failure
- **Polling Frequency:** Monitor API call volume
- **Error Rate:** Track failed message sends

### User Feedback
- **Chat Responsiveness Rating:** In-app survey
- **Support Tickets:** Track chat-related complaints
- **App Store Reviews:** Monitor chat mentions

---

## 🔮 Future Enhancements (Post-MVP)

### Phase 2: WebSocket (Optional)
**When:** If users complain about 5-second delay in active chats  
**Implementation:** Laravel Reverb + Echo  
**Cost:** +$10-20/month for WebSocket server  
**Benefit:** Truly real-time (sub-second) for active chats

### Phase 3: Message Read Receipts
**When:** After MVP launch  
**Implementation:** Add `read_at` timestamp update via API  
**Benefit:** Double-check marks like WhatsApp

### Phase 4: Typing Indicators
**When:** If chat usage high  
**Implementation:** WebSocket or frequent polling (2s)  
**Benefit:** "Typing..." indicator

### Phase 5: Message Reactions
**When:** Advanced feature request  
**Implementation:** Add reactions table, API endpoints  
**Benefit:** Emoji reactions to messages

---

## 📝 Notes

### Why NOT Firestore for Chat Data?
1. **Cost:** Free tier limited (50K reads/day, 20K writes/day)
2. **Complexity:** Dual database sync (MySQL ↔ Firestore)
3. **Vendor Lock-in:** Hard to migrate from Firebase
4. **Query Limitations:** Firestore queries less flexible than SQL

### Why MySQL + FCM is Better for MVP?
1. **Cost:** Free (FCM is free, MySQL already hosted)
2. **Simplicity:** One database, easy to backup/query
3. **Control:** Full control over data and queries
4. **Scalability:** Can upgrade to WebSocket later if needed

### Psychological Trick
- User **sees notification** instantly (FCM)
- User **takes 2-5 seconds** to read & tap
- By the time app opens, **polling already fetched data**
- User **perceives** it as real-time
- **Reality:** Backend used simple polling + instant notification

---

## ✅ Approval

**Approved by:** Aswar  
**Date:** 28 Februari 2026  
**Status:** Ready for Implementation  

**Next Step:** Start Phase 1 - Backend FCM Integration

---

**Last Updated:** 28 Februari 2026  
**Version:** 1.0  
**Owner:** Aswar
