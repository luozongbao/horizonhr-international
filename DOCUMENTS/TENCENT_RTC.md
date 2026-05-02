# Research: Tencent RTC Meeting vs Conference

**Task:** FEAT-022 - Research for Video Interview and Seminar System 
**Date:** 2026-04-18
**Status:** COMPLETED

---

## Executive Summary

**Recommendation:** Use **Tencent RTC (TRTC)** for both 1-on-1 Video Interview and Online Seminar

TRTC provides the flexibility needed for custom video interview flows and supports large-scale live streaming for seminars.

---

## Product Comparison

### 1. Tencent Meeting (腾讯会议)

**What it is:** A finished SaaS video conferencing product with admin controls, scheduling, and meeting management.

**API Type:** REST API + Webhook for meeting management

**Key Features:**
- Meeting scheduling and management
- Attendance tracking
- Screen sharing
- Recording
- Admin dashboard

**Limitations:**
- Not designed for custom application integration
- Limited control over the UI/UX
- Meeting-centric, not user-centric
- REST API only, no WebRTC SDK for custom embedding

**Best for:**
- Quick deployment of standard video meetings
- Companies needing out-of-the-box video conferencing

**Pricing:** Based on meeting minutes, Enterprise plans available

### 2. Tencent RTC (TRTC)

**What it is:** Real-Time Communication platform with WebRTC SDK for building custom video applications.

**API Type:** WebRTC SDK + REST API

**Key Features:**
- 1-on-1 video calls
- Group video calls (up to 300 users)
- Live streaming (up to 100,000 viewers)
- Screen sharing
- Interactive whiteboard (optional)
- Custom UIKits available
- Cross-platform (Web, iOS, Android, Windows, macOS)

**Limitations:**
- Requires more development effort
- Need to build your own meeting room UI

**Best for:**
- Building custom video applications
- Embedding video into existing apps
- Scalable live streaming

**Pricing:**
- Audio call: ¥0.99/1,000 minutes
- Video call 720P: ¥3.99/1,000 minutes
- Video call 1080P: ¥7.99/1,000 minutes
- Live streaming: ¥1.49/1,000 minutes

---

## Use Case Analysis

### 1-on-1 Video Interview

**Requirements:**
- 1-on-1 video interview
- WebRTC based
- Interview scheduling and management
- Recording capability (optional)

**Recommended Solution:** Tencent RTC (TRTC)

**Reasoning:**
1. TRTC supports high-quality 1-on-1 video calls
2. Can build custom interview flow UI
3. REST API for room management and scheduling
4. Supports recording via cloud MIX
5. Lower cost for 1-on-1 calls

**Integration with Laravel:**
```php
// Backend: Create room and generate user signature
public function createInterview(Request $request)
{
    $roomId = 'interview_' . uniqid();
    $userId = auth()->id();
    
    // TRTC signature generation (server-side)
    $signature = $this->generateTRTCSignature($userId);
    
    return response()->json([
        'success' => true,
        'data' => [
            'roomId' => $roomId,
            'userId' => (string) $userId,
            'signature' => $signature,
            'sdkAppId' => config('services.tencent.sdk_app_id')
        ]
    ]);
}
```

**Frontend Integration:**
```javascript
import TRTC from 'trtc-sdk-js'

const trtc = TRTC.create({
  appId: data.sdkAppId,
  userId: data.userId,
  userSig: data.signature
})

await trtc.enterRoom({ roomId: data.roomId, role: 'anchor' })
```

---

### Online Seminar (Live + Playback, 10,000+ viewers)

**Requirements:**
- Live streaming to 10,000+ viewers
- Interactive features (Q&A, polling)
- Recording and playback
- CDN distribution

**Recommended Solution:** Tencent RTC (TRTC) with CDN

**Reasoning:**
1. TRTC supports up to 100,000 concurrent viewers in live streaming mode
2. CDN push for global distribution
3. Supports RTMP/HLS for playback
4. Interactive features can be built on top
5. Mix stream for picture-in-picture (speaker + slides)

**Integration with Laravel:**
```php
// Backend: Create live stream and get push URL
public function createSeminar(Request $request)
{
    $streamId = 'seminar_' . uniqid();
    
    return response()->json([
        'success' => true,
        'data' => [
            'streamId' => $streamId,
            'pushUrl' => $this->generatePushUrl($streamId),
            'playDomain' => config('services.tencent.play_domain')
        ]
    ]);
}
```

**Architecture for 10,000+ Viewers:**
```
Broadcaster → TRTC Server → CDN → Viewers
                    ↓
            Recording (Optional)
```

---

## API Endpoints Reference

### Tencent RTC REST API

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/v1/apps/{appId}/rooms` | POST | Create room |
| `/v1/apps/{appId}/rooms/{roomId}` | DELETE | Delete room |
| `/v1/apps/{appId}/users/{userId}/rooms` | GET | Get user's rooms |
| `/v1/sign/` | POST | Generate user signature |

### Tencent Meeting API

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/v1/meetings` | POST | Create meeting |
| `/v1/meetings/{meetingId}` | GET | Get meeting info |
| `/v1/meetings/{meetingId}/cancel` | POST | Cancel meeting |
| `/v1/meetings/{meetingId}/join` | GET | Get join link |

---

## Implementation Roadmap

### Phase 1: Video Interview
1. Set up TRTC console and get credentials
2. Install SDK: `npm install trtc-sdk-js`
3. Create Laravel API for room management
4. Build interview UI with Vue.js
5. Test 1-on-1 video quality
6. Add recording capability

### Phase 2: Online Seminar
1. Configure live streaming CDN
2. Set up RTMP push
3. Build seminar UI (stream viewer, Q&A panel)
4. Implement viewer count handling
5. Add recording and playback

---

## Conclusion

**Tencent RTC (TRTC)** is the recommended solution because:

1. **Flexibility:** Build custom UI matching design
2. **Scalability:** Support 10,000+ viewers for seminars
3. **Cost-effective:** Pay per minute, no per-host licensing
4. **Integration:** Seamless Laravel backend integration
5. **Features:** Recording, screen share, whiteboard available

Tencent Meeting would require embedding via iframe which limits customization and doesn't support the seminar use case well.

---

## References

- TRTC Web SDK: https://web.sdk.qcloud.com/trtc/webrtc/doc/en/
- TRTC Console: https://console.cloud.tencent.com/trtc
- Pricing: https://buy.cloud.tencent.com/trtc
- GitHub: https://github.com/Tencent-RTC
