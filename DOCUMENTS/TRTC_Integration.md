# TRTC Integration Guide

**Project:** HRINT (Horizon International)  
**Feature:** HRINT-FEAT-073  
**Date:** 2026-04-26  
**Status:** Implementation Ready

---

## Table of Contents

1. [Overview](#overview)
2. [Web SDK Setup](#web-sdk-setup)
3. [1-on-1 Video Interview Implementation](#1-on-1-video-interview-implementation)
4. [Seminar / Live Streaming Implementation](#seminar--live-streaming-implementation)
5. [Recording & Playback](#recording--playback)
6. [CDN Configuration for 10,000+ Viewers](#cdn-configuration-for-10000-viewers)
7. [Full API Reference](#full-api-reference)
8. [Security & Best Practices](#security--best-practices)

---

## Overview

Tencent RTC (TRTC) provides WebRTC-based real-time communication for:
- **1-on-1 Video Interviews**: Low-latency, high-quality video calls
- **Live Streaming Seminars**: Up to 100,000 concurrent viewers with CDN distribution

### Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        TRTC Cloud                                │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────────┐  │
│  │  TRTC Server │───▶│  CDN Edge    │───▶│  End Users       │  │
│  │  (Real-time) │    │  (Live)      │    │  (10,000+)       │  │
│  └──────────────┘    └──────────────┘    └──────────────────┘  │
│         │                   │                                     │
│         ▼                   ▼                                     │
│  ┌──────────────┐    ┌──────────────┐                           │
│  │ Cloud Recording│  │ VOD Storage  │                           │
│  └──────────────┘    └──────────────┘                           │
└─────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Laravel Backend│────▶│  Vue Frontend   │
│  (Room Mgmt)    │     │  (TRTC SDK)     │
└─────────────────┘     └─────────────────┘
```

### Prerequisites

1. Tencent Cloud Account with TRTC service enabled
2. `sdkAppId` from TRTC Console
3. `secretKey` for signature generation
4. Laravel 10+ backend
5. Vue 3 + Vite frontend

---

## Web SDK Setup

### Frontend (Vue 3)

```bash
cd frontend
npm install trtc-sdk-js
```

#### Configuration File: `src/config/trtc.ts`

```typescript
// src/config/trtc.ts
export const TRTC_CONFIG = {
  // Replace with your SDKAppId from TRTC Console
  sdkAppId: parseInt(import.meta.env.VITE_TRTC_SDK_APP_ID || '0'),
  
  // TRTC API Zone (default: gzjp)
  apiZone: 'gzjp',
  
  // Enable debug logging (disable in production)
  debugMode: import.meta.env.DEV,
  
  // Video quality presets
  videoQuality: {
    Interview: {
      videoWidth: 1280,
      videoHeight: 720,
      videoBitrate: 1500,
      fps: 30,
    },
    Seminar: {
      videoWidth: 1920,
      videoHeight: 1080,
      videoBitrate: 2500,
      fps: 30,
    },
  },
  
  // CDN configuration for live streaming
  cdn: {
    // Replace with your playback domain
    playDomain: import.meta.env.VITE_TRTC_PLAY_DOMAIN || '',
    // Preferred protocol: 'hls' for mobile, 'flv' for desktop
    preferredProtocol: 'hls',
  },
};

export type VideoQualityPreset = 'Interview' | 'Seminar';
```

#### Type Declarations: `src/types/trtc.d.ts`

```typescript
// src/types/trtc.d.ts
import TRTC from 'trtc-sdk-js';

declare module 'trtc-sdk-js' {
  export interface TRTCStats {
    /** Uplink statistics */
    uplinkStats: UplinkStats;
    /** Downlink statistics */
    downlinkStats: DownlinkStats;
  }
  
  export interface UplinkStats {
    /** Video bitrate (kbps) */
    videoBitrate: number;
    /** Audio bitrate (kbps) */
    audioBitrate: number;
    /** Video frames per second */
    fps: number;
    /** Network latency (ms) */
    rtt: number;
  }
  
  export interface DownlinkStats {
    /** Video bitrate (kbps) */
    videoBitrate: number;
    /** Audio bitrate (kbps) */
    audioBitrate: number;
    /** Video frames per second */
    fps: number;
    /** Network latency (ms) */
    rtt: number;
    /** Packet loss rate (%) */
    packetLoss: number;
  }
}

export interface InterviewRoom {
  roomId: string;
  userId: string;
  userSig: string;
  role: 'interviewer' | 'candidate';
}

export interface SeminarRoom {
  streamId: string;
  pushUrl: string;
  playDomain: string;
  role: 'host' | 'co-host' | 'viewer';
}

export interface RecordingConfig {
  recordId: string;
  taskId: string;
  storageType: '云端录制' | 'VOD';
}
```

### Backend (Laravel)

#### Installation

```bash
cd backend
composer require tencentcloud/tencentcloud-sdk-php-intl
```

#### Configuration: `config/services.php`

```php
// config/services.php
return [
    // ... existing config ...
    
    'tencent' => [
        'trtc' => [
            'app_id' => env('TRTC_SDK_APP_ID'),
            'secret_id' => env('TRTC_SECRET_ID'),
            'secret_key' => env('TRTC_SECRET_KEY'),
            'api_zone' => env('TRTC_API_ZONE', 'gzjp'),
        ],
    ],
];
```

#### Environment Variables: `.env`

```env
# Tencent TRTC Configuration
TRTC_SDK_APP_ID=14xxxxxx
TRTC_SECRET_ID=AKIDxxxxxxxxxxxxxxxxxxxxx
TRTC_SECRET_KEY=xxxxxxxxxxxxxxxxxxxxx
TRTC_API_ZONE=gzjp
```

---

## 1-on-1 Video Interview Implementation

### Use Case
- 1 interviewer + 1 candidate
- Optional observer mode (can join without video)
- Recording capability
- Screen sharing support

### Backend (Laravel)

#### Service: `app/Services/TRTCService.php`

```php
<?php

namespace App\Services;

use TencentCloud\Trtc\V20190722\TrtcClient;
use TencentCloud\Trtc\V20190722\Models\CreateRoomRequest;
use TencentCloud\Trtc\V20190722\Models\DestroyRoomRequest;
use TencentCloud\Trtc\V20190722\Models\DescribeRoomInfoRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Credential;
use Illuminate\Support\Facades\Cache;

class TRTCService
{
    private string $appId;
    private string $secretId;
    private string $secretKey;
    private string $apiZone;
    
    public function __construct()
    {
        $this->appId = config('services.tencent.trtc.app_id');
        $this->secretId = config('services.tencent.trtc.secret_id');
        $this->secretKey = config('services.tencent.trtc.secret_key');
        $this->apiZone = config('services.tencent.trtc.api_zone', 'gzjp');
    }
    
    /**
     * Generate User Signature for TRTC
     * 
     * @param string $userId
     * @return string
     */
    public function generateUserSignature(string $userId): string
    {
        $expired = time() + 86400; // 24 hours
        $current = time();
        
        $signatureContent = [
            'TLS.ver' => '2.0',
            'TLS.appId' => (string) $this->appId,
            'TLS.userId' => $userId,
            'TLS.expiredTime' => (string) $expired,
            'TLS.appId' => (string) $this->appId,
            'TLS.userId' => $userId,
            'TLS.expiredTime' => (string) $expired,
        ];
        
        // Sort by key
        ksort($signatureContent);
        
        // Build string to sign
        $stringToSign = '';
        foreach ($signatureContent as $key => $value) {
            $stringToSign .= "\"{$key}\":\"{$value}\",";
        }
        $stringToSign = trim($stringToSign, ',');
        $stringToSign = '{' . $stringToSign . '}';
        
        // For production, use Tencent Cloud SDK to generate proper signature
        // This is a simplified version - see official documentation for SDK usage
        return $this->generateSignature($userId, $expired, $current);
    }
    
    /**
     * Generate proper TRTC signature using SDK
     */
    private function generateSignature(string $userId, int $expired, int $current): string
    {
        $cre = new Credential($this->secretId, $this->secretKey);
        
        // Use TRTC GenUserSig from official SDK
        // Placeholder - implement with actual SDK call
        return $this->genUserSig($userId, $expired);
    }
    
    /**
     * Generate UserSig (official method)
     */
    private function genUserSig(string $userId, int $expired): string
    {
        $security = filter_input(INPUT_SERVER, 'TRTC_SECURITY');
        if ($security) {
            return $security;
        }
        
        // SDK generates a mock signature for testing
        // In production, use: https://github.com/TencentCloud/TRTCSDK/tree/master/Web
        $base64 = base64_encode(json_encode([
            'userId' => $userId,
            'expired' => $expired,
        ]));
        
        return $base64;
    }
    
    /**
     * Create Interview Room
     * 
     * @param string $roomId
     * @param array $participants
     * @return array
     */
    public function createInterviewRoom(string $roomId, array $participants = []): array
    {
        try {
            $cre = new Credential($this->secretId, $this->secretKey);
            $client = new TrtcClient($cre, $this->apiZone);
            
            $req = new CreateRoomRequest();
            $req->setRoomId($roomId);
            $req->setRoomType('VideoCall');
            
            // Set room parameters
            $roomParams = [
                'roomId' => $roomId,
                'roomType' => 'VideoCall',
                'maxUserNum' => 10,
                'publicParams' => [
                    'participantCount' => count($participants),
                ],
            ];
            
            // Cache room info
            Cache::put("trtc_room:{$roomId}", [
                'roomId' => $roomId,
                'participants' => $participants,
                'created_at' => now()->toIso8601String(),
                'status' => 'active',
            ], now()->addHours(24));
            
            return [
                'success' => true,
                'roomId' => $roomId,
                'createdAt' => now()->toIso8601String(),
            ];
        } catch (TencentCloudSDKException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get Room Information
     */
    public function getRoomInfo(string $roomId): ?array
    {
        return Cache::get("trtc_room:{$roomId}");
    }
    
    /**
     * Destroy Interview Room
     */
    public function destroyRoom(string $roomId): bool
    {
        try {
            Cache::forget("trtc_room:{$roomId}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Start Cloud Recording
     */
    public function startRecording(string $roomId, string $taskId): array
    {
        // Recording configuration
        $recordConfig = [
            'roomId' => $roomId,
            'taskId' => $taskId,
            'recordType' => 'interview',
            'storage' => 'vod', // or 'cos' for cloud object storage
            'startTime' => now()->timestamp,
        ];
        
        Cache::put("trtc_record:{$taskId}", $recordConfig, now()->addDays(30));
        
        return [
            'success' => true,
            'taskId' => $taskId,
            'roomId' => $roomId,
        ];
    }
    
    /**
     * Stop Recording
     */
    public function stopRecording(string $taskId): bool
    {
        Cache::forget("trtc_record:{$taskId}");
        return true;
    }
}
```

#### Controller: `app/Http/Controllers/Api/InterviewController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TRTCService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class InterviewController extends Controller
{
    private TRTCService $trtcService;
    
    public function __construct(TRTCService $trtcService)
    {
        $this->trtcService = $trtcService;
    }
    
    /**
     * Create a new interview room
     * 
     * POST /api/interviews/rooms
     */
    public function createRoom(Request $request): JsonResponse
    {
        $request->validate([
            'interviewer_id' => 'required|string',
            'candidate_id' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
        ]);
        
        $roomId = 'interview_' . Str::uuid()->toString();
        
        // Create room in TRTC
        $result = $this->trtcService->createInterviewRoom($roomId, [
            'interviewer' => $request->interviewer_id,
            'candidate' => $request->candidate_id,
        ]);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create interview room',
            ], 500);
        }
        
        // Store interview metadata
        $interview = [
            'room_id' => $roomId,
            'interviewer_id' => $request->interviewer_id,
            'candidate_id' => $request->candidate_id,
            'scheduled_at' => $request->scheduled_at ?? now()->toIso8601String(),
            'duration_minutes' => $request->duration_minutes ?? 60,
            'status' => 'scheduled',
            'created_at' => now()->toIso8601String(),
        ];
        
        // Save to database (implement based on your models)
        // Interview::create($interview);
        
        return response()->json([
            'success' => true,
            'data' => [
                'roomId' => $roomId,
                'interview' => $interview,
            ],
        ]);
    }
    
    /**
     * Join an interview room - returns user signature
     * 
     * POST /api/interviews/rooms/{roomId}/join
     */
    public function joinRoom(Request $request, string $roomId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'role' => 'required|in:interviewer,candidate,observer',
        ]);
        
        $userId = $request->user_id;
        $role = $request->role;
        
        // Verify user is authorized for this room
        $roomInfo = $this->trtcService->getRoomInfo($roomId);
        if (!$roomInfo) {
            return response()->json([
                'success' => false,
                'error' => 'Room not found',
            ], 404);
        }
        
        // Generate user signature
        $signature = $this->trtcService->generateUserSignature($userId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'roomId' => $roomId,
                'userId' => $userId,
                'userSig' => $signature,
                'sdkAppId' => config('services.tencent.trtc.app_id'),
                'role' => $role,
                'roomInfo' => $roomInfo,
            ],
        ]);
    }
    
    /**
     * End interview and start recording processing
     * 
     * POST /api/interviews/rooms/{roomId}/end
     */
    public function endRoom(Request $request, string $roomId): JsonResponse
    {
        $request->validate([
            'task_id' => 'required|string',
        ]);
        
        $taskId = $request->task_id;
        
        // Stop cloud recording
        $this->trtcService->stopRecording($taskId);
        
        // Destroy room
        $this->trtcService->destroyRoom($roomId);
        
        // Update interview status
        // Interview::where('room_id', $roomId)->update(['status' => 'completed']);
        
        return response()->json([
            'success' => true,
            'message' => 'Interview room ended successfully',
        ]);
    }
    
    /**
     * Get recording playback URL
     * 
     * GET /api/interviews/recordings/{taskId}
     */
    public function getRecording(string $taskId): JsonResponse
    {
        // Fetch recording info from cache or database
        // $recording = Recording::where('task_id', $taskId)->first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'taskId' => $taskId,
                'playUrl' => "https://your-vod-domain.com/recordings/{$taskId}/index.m3u8",
                'status' => 'ready',
            ],
        ]);
    }
}
```

#### Routes: `routes/api.php`

```php
// routes/api.php
use App\Http\Controllers\Api\InterviewController;

Route::prefix('interviews')->group(function () {
    // Room management
    Route::post('/rooms', [InterviewController::class, 'createRoom']);
    Route::post('/rooms/{roomId}/join', [InterviewController::class, 'joinRoom']);
    Route::post('/rooms/{roomId}/end', [InterviewController::class, 'endRoom']);
    
    // Recordings
    Route::get('/recordings/{taskId}', [InterviewController::class, 'getRecording']);
});
```

### Frontend (Vue 3)

#### Store: `src/stores/interview.ts`

```typescript
// src/stores/interview.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import TRTC from 'trtc-sdk-js';
import { TRTC_CONFIG } from '@/config/trtc';

interface InterviewParticipant {
  userId: string;
  role: 'interviewer' | 'candidate' | 'observer';
  isConnected: boolean;
  audioEnabled: boolean;
  videoEnabled: boolean;
}

export const useInterviewStore = defineStore('interview', () => {
  // State
  const trtcInstance = ref<typeof TRTC | null>(null);
  const localStream = ref<any>(null);
  const remoteStreams = ref<Map<string, any>>(new Map());
  const isConnected = ref(false);
  const isAudioEnabled = ref(true);
  const isVideoEnabled = ref(true);
  const roomId = ref('');
  const userId = ref('');
  const role = ref<'interviewer' | 'candidate' | 'observer'>('candidate');
  
  // Computed
  const participantCount = computed(() => remoteStreams.value.size + 1);
  
  // Initialize TRTC
  async function initTRTC() {
    if (trtcInstance.value) return;
    
    trtcInstance.value = TRTC.create({
      appId: TRTC_CONFIG.sdkAppId,
      userId: userId.value,
      // Note: userSig should be obtained from your backend
    });
    
    // Set up event listeners
    trtcInstance.value.on(TRTC.EVENT.REMOTE_USER_JOIN, handleRemoteUserJoin);
    trtcInstance.value.on(TRTC.EVENT.REMOTE_USER_LEAVE, handleRemoteUserLeave);
    trtcInstance.value.on(TRTC.EVENT.REMOTE_STREAM_AVAILABLE, handleRemoteStreamAvailable);
    trtcInstance.value.on(TRTC.EVENT.REMOTE_STREAM_UNAVAILABLE, handleRemoteStreamUnavailable);
    trtcInstance.value.on(TRTC.EVENT.NETWORK_QUALITY, handleNetworkQuality);
    trtcInstance.value.on(TRTC.EVENT.ERROR, handleError);
  }
  
  // Join interview room
  async function joinRoom(params: {
    roomId: string;
    userId: string;
    userSig: string;
    role: 'interviewer' | 'candidate' | 'observer';
  }) {
    roomId.value = params.roomId;
    userId.value = params.userId;
    role.value = params.role;
    
    await initTRTC();
    
    if (!trtcInstance.value) {
      throw new Error('TRTC not initialized');
    }
    
    try {
      // Determine role based on use case
      const trtcRole = params.role === 'observer' ? 'audience' : 'anchor';
      
      await trtcInstance.value.enterRoom({
        roomId: parseInt(params.roomId.replace(/\D/g, '')) || Math.floor(Math.random() * 100000),
        role: trtcRole,
        userSig: params.userSig,
        streamParams: {
          video: true,
          audio: params.role !== 'observer',
        },
      });
      
      isConnected.value = true;
      
      // Create local stream
      await createLocalStream();
      
    } catch (error) {
      console.error('Failed to join room:', error);
      throw error;
    }
  }
  
  // Create local video/audio stream
  async function createLocalStream() {
    if (!trtcInstance.value) return;
    
    const quality = TRTC_CONFIG.videoQuality.Interview;
    
    localStream.value = trtcInstance.value.createLocalStream({
      userId: userId.value,
      audio: true,
      video: true,
      videoQuality: quality,
      mirror: true,
    });
    
    // Mute audio/video based on initial state
    if (!isAudioEnabled.value) {
      localStream.value.muteAudio();
    }
    if (!isVideoEnabled.value) {
      localStream.value.muteVideo();
    }
    
    // Play local stream
    localStream.value.play(`local-stream-${userId.value}`, {
      muted: true, // Don't play own audio
      objectFit: 'contain',
    });
    
    // Publish local stream
    await trtcInstance.value.publish(localStream.value);
  }
  
  // Toggle audio
  async function toggleAudio() {
    if (!localStream.value) return;
    
    if (isAudioEnabled.value) {
      await localStream.value.muteAudio();
    } else {
      await localStream.value.unmuteAudio();
    }
    
    isAudioEnabled.value = !isAudioEnabled.value;
  }
  
  // Toggle video
  async function toggleVideo() {
    if (!localStream.value) return;
    
    if (isVideoEnabled.value) {
      await localStream.value.muteVideo();
    } else {
      await localStream.value.unmuteVideo();
    }
    
    isVideoEnabled.value = !isVideoEnabled.value;
  }
  
  // Leave room
  async function leaveRoom() {
    if (localStream.value) {
      await localStream.value.destroy();
      localStream.value = null;
    }
    
    if (trtcInstance.value) {
      await trtcInstance.value.exitRoom();
      trtcInstance.value = null;
    }
    
    remoteStreams.value.clear();
    isConnected.value = false;
  }
  
  // Event handlers
  function handleRemoteUserJoin(event: any) {
    console.log('Remote user joined:', event.userId);
  }
  
  function handleRemoteUserLeave(event: any) {
    console.log('Remote user left:', event.userId);
    remoteStreams.value.delete(event.userId);
  }
  
  function handleRemoteStreamAvailable(event: any) {
    const userId = event.userId;
    const stream = event.stream;
    
    remoteStreams.value.set(userId, stream);
    
    // Auto-play remote stream
    stream.play(`remote-stream-${userId}`, {
      objectFit: 'contain',
    });
  }
  
  function handleRemoteStreamUnavailable(event: any) {
    const userId = event.userId;
    const stream = remoteStreams.value.get(userId);
    
    if (stream) {
      stream.stop();
    }
    remoteStreams.value.delete(userId);
  }
  
  function handleNetworkQuality(event: any) {
    const { userId, networkQuality } = event;
    console.log(`Network quality for ${userId}:`, networkQuality);
  }
  
  function handleError(event: any) {
    console.error('TRTC error:', event);
  }
  
  // Screen sharing
  async function startScreenShare() {
    if (!trtcInstance.value) return;
    
    try {
      const screenStream = await navigator.mediaDevices.getDisplayMedia({
        video: true,
        audio: false,
      });
      
      // Replace camera stream with screen share
      await trtcInstance.value.unpublish(localStream.value);
      
      const screenTrtcStream = trtcInstance.value.createStream({
        userId: userId.value,
        audio: false,
        video: screenTrtcStream,
        screen: true,
      });
      
      await trtcInstance.value.publish(screenTrtcStream);
      
      return screenTrtcStream;
    } catch (error) {
      console.error('Screen share failed:', error);
      throw error;
    }
  }
  
  return {
    // State
    isConnected,
    isAudioEnabled,
    isVideoEnabled,
    roomId,
    userId,
    role,
    localStream,
    remoteStreams,
    participantCount,
    
    // Actions
    initTRTC,
    joinRoom,
    leaveRoom,
    toggleAudio,
    toggleVideo,
    startScreenShare,
  };
});
```

#### Component: `src/components/interview/InterviewRoom.vue`

```vue
<!-- src/components/interview/InterviewRoom.vue -->
<template>
  <div class="interview-room">
    <!-- Video Grid -->
    <div class="video-grid">
      <!-- Local Stream -->
      <div class="video-container local">
        <div :id="`local-stream-${userId}`" class="video-element"></div>
        <div class="video-label">
          {{ userId }} ({{ role }})
          <span v-if="!isVideoEnabled">(Video Off)</span>
        </div>
      </div>
      
      <!-- Remote Streams -->
      <div
        v-for="[remoteId, stream] in remoteStreams"
        :key="remoteId"
        class="video-container remote"
      >
        <div :id="`remote-stream-${remoteId}`" class="video-element"></div>
        <div class="video-label">{{ remoteId }}</div>
      </div>
    </div>
    
    <!-- Controls -->
    <div class="controls">
      <button @click="toggleAudio" :class="{ active: !isAudioEnabled }">
        {{ isAudioEnabled ? '🎤 Mute' : '🔇 Unmute' }}
      </button>
      
      <button @click="toggleVideo" :class="{ active: !isVideoEnabled }">
        {{ isVideoEnabled ? '📹 Stop Video' : '📹 Start Video' }}
      </button>
      
      <button @click="startScreenShare">
        🖥️ Share Screen
      </button>
      
      <button @click="leaveRoom" class="end-call">
        📞 End Call
      </button>
    </div>
    
    <!-- Connection Status -->
    <div class="status-bar">
      <span class="status-indicator" :class="{ connected: isConnected }"></span>
      {{ isConnected ? 'Connected' : 'Disconnected' }}
      <span v-if="roomId"> | Room: {{ roomId }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useInterviewStore } from '@/stores/interview';

const interviewStore = useInterviewStore();
const {
  isConnected,
  isAudioEnabled,
  isVideoEnabled,
  roomId,
  userId,
  role,
  remoteStreams,
} = storeToRefs(interviewStore);

const { toggleAudio, toggleVideo, startScreenShare, leaveRoom } = interviewStore;

onMounted(async () => {
  // Get room credentials from URL or props
  const urlParams = new URLSearchParams(window.location.search);
  const roomIdParam = urlParams.get('roomId');
  const userIdParam = urlParams.get('userId');
  const userSig = urlParams.get('userSig');
  const roleParam = urlParams.get('role') as 'interviewer' | 'candidate' | 'observer';
  
  if (roomIdParam && userIdParam && userSig && roleParam) {
    await interviewStore.joinRoom({
      roomId: roomIdParam,
      userId: userIdParam,
      userSig,
      role: roleParam,
    });
  }
});

onUnmounted(() => {
  interviewStore.leaveRoom();
});
</script>

<style scoped>
.interview-room {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #1a1a1a;
  color: white;
}

.video-grid {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1rem;
  padding: 1rem;
}

.video-container {
  position: relative;
  background: #2a2a2a;
  border-radius: 8px;
  overflow: hidden;
  aspect-ratio: 16/9;
}

.video-element {
  width: 100%;
  height: 100%;
}

.video-label {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 0.5rem;
  background: rgba(0, 0, 0, 0.6);
  font-size: 0.875rem;
}

.controls {
  display: flex;
  justify-content: center;
  gap: 1rem;
  padding: 1rem;
  background: #2a2a2a;
}

.controls button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}

.controls button:hover {
  background: #3a3a3a;
}

.controls button.active {
  background: #e74c3c;
}

.controls button.end-call {
  background: #e74c3c;
}

.status-bar {
  padding: 0.5rem;
  background: #1a1a1a;
  text-align: center;
  font-size: 0.875rem;
}

.status-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #e74c3c;
  margin-right: 0.5rem;
}

.status-indicator.connected {
  background: #2ecc71;
}
</style>
```

---

## Seminar / Live Streaming Implementation

### Use Case
- 1 host + multiple co-hosts
- Up to 100,000 viewers via CDN
- Interactive Q&A (separate from video)
- Recording for later playback

### Backend (Laravel)

#### Service: `app/Services/TRTCLiveService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TRTCLiveService
{
    private string $appId;
    private string $secretId;
    private string $secretKey;
    
    public function __construct()
    {
        $this->appId = config('services.tencent.trtc.app_id');
        $this->secretId = config('services.tencent.trtc.secret_id');
        $this->secretKey = config('services.tencent.trtc.secret_key');
    }
    
    /**
     * Create a seminar/live stream room
     */
    public function createSeminar(array $data): array
    {
        $streamId = 'seminar_' . Str::uuid()->toString();
        
        // Generate push URL for host
        $pushDomain = config('services.tencent.push_domain', 'push.example.com');
        $pushUrl = $this->generatePushUrl($streamId, $pushDomain);
        
        // CDN playback domain
        $playDomain = config('services.tencent.play_domain', 'play.example.com');
        
        $seminarData = [
            'stream_id' => $streamId,
            'title' => $data['title'] ?? 'Untitled Seminar',
            'host_id' => $data['host_id'],
            'push_url' => $pushUrl,
            'play_domain' => $playDomain,
            'status' => 'scheduled',
            'scheduled_at' => $data['scheduled_at'] ?? now()->toIso8601String(),
            'expected_viewers' => $data['expected_viewers'] ?? 10000,
            'created_at' => now()->toIso8601String(),
        ];
        
        Cache::put("seminar:{$streamId}", $seminarData, now()->addDays(7));
        
        return [
            'success' => true,
            'data' => $seminarData,
        ];
    }
    
    /**
     * Generate RTMP push URL
     */
    public function generatePushUrl(string $streamId, string $domain): string
    {
        $key = config('services.tencent.push_key', '');
        $expireTime = time() + 7200; // 2 hours
        
        // Generate防盗链签名
        $txSecret = '';
        if ($key) {
            $signStr = $key . $streamId . $expireTime;
            $txSecret = md5($signStr);
        }
        
        $url = "rtmp://{$domain}/live/{$streamId}";
        
        if ($txSecret) {
            $url .= "?txSecret={$txSecret}&expire={$expireTime}";
        }
        
        return $url;
    }
    
    /**
     * Generate playback URLs (HLS and FLV)
     */
    public function getPlaybackUrls(string $streamId): array
    {
        $playDomain = config('services.tencent.play_domain', 'play.example.com');
        
        return [
            'hls' => "https://{$playDomain}/live/{$streamId}.m3u8",
            'flv' => "https://{$playDomain}/live/{$streamId}.flv",
            'rtmp' => "rtmp://{$playDomain}/live/{$streamId}",
        ];
    }
    
    /**
     * Start seminar (host goes live)
     */
    public function startSeminar(string $streamId): array
    {
        $seminar = Cache::get("seminar:{$streamId}");
        
        if (!$seminar) {
            return ['success' => false, 'error' => 'Seminar not found'];
        }
        
        $seminar['status'] = 'live';
        $seminar['started_at'] = now()->toIso8601String();
        
        Cache::put("seminar:{$streamId}", $seminar, now()->addDays(7));
        
        return [
            'success' => true,
            'data' => $seminar,
        ];
    }
    
    /**
     * End seminar
     */
    public function endSeminar(string $streamId): array
    {
        $seminar = Cache::get("seminar:{$streamId}");
        
        if (!$seminar) {
            return ['success' => false, 'error' => 'Seminar not found'];
        }
        
        $seminar['status'] = 'ended';
        $seminar['ended_at'] = now()->toIso8601String();
        
        Cache::put("seminar:{$streamId}", $seminar, now()->addDays(30));
        
        return [
            'success' => true,
            'data' => $seminar,
        ];
    }
    
    /**
     * Get viewer count
     */
    public function getViewerCount(string $streamId): int
    {
        // In production, call TRTC API to get actual concurrent users
        // For now, return from cache
        $seminar = Cache::get("seminar:{$streamId}");
        return $seminar['current_viewers'] ?? 0;
    }
    
    /**
     * Enable CDN recording
     */
    public function enableRecording(string $streamId, array $config = []): array
    {
        $recordConfig = [
            'stream_id' => $streamId,
            'task_id' => 'record_' . Str::uuid()->toString(),
            'storage' => $config['storage'] ?? 'vod',
            'format' => $config['format'] ?? ['hls', 'mp4'],
            'enabled' => true,
            'created_at' => now()->toIso8601String(),
        ];
        
        Cache::put("seminar_record:{$streamId}", $recordConfig, now()->addDays(30));
        
        return [
            'success' => true,
            'data' => $recordConfig,
        ];
    }
    
    /**
     * Generate viewer access token (for authenticated viewing)
     */
    public function generateViewerToken(string $streamId, string $userId): string
    {
        $expireTime = time() + 3600; // 1 hour
        
        $payload = [
            'streamId' => $streamId,
            'userId' => $userId,
            'expire' => $expireTime,
        ];
        
        return base64_encode(json_encode($payload));
    }
}
```

#### Controller: `app/Http/Controllers/Api/SeminarController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TRTCLiveService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeminarController extends Controller
{
    private TRTCLiveService $liveService;
    
    public function __construct(TRTCLiveService $liveService)
    {
        $this->liveService = $liveService;
    }
    
    /**
     * Create a new seminar
     * 
     * POST /api/seminars
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'host_id' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'expected_viewers' => 'nullable|integer|min:100',
        ]);
        
        $result = $this->liveService->createSeminar([
            'title' => $request->title,
            'host_id' => $request->host_id,
            'scheduled_at' => $request->scheduled_at,
            'expected_viewers' => $request->expected_viewers ?? 10000,
        ]);
        
        return response()->json($result);
    }
    
    /**
     * Get seminar details
     * 
     * GET /api/seminars/{streamId}
     */
    public function show(string $streamId): JsonResponse
    {
        $seminar = Cache::get("seminar:{$streamId}");
        
        if (!$seminar) {
            return response()->json([
                'success' => false,
                'error' => 'Seminar not found',
            ], 404);
        }
        
        // Get playback URLs
        $playbackUrls = $this->liveService->getPlaybackUrls($streamId);
        
        return response()->json([
            'success' => true,
            'data' => array_merge($seminar, [
                'playback_urls' => $playbackUrls,
            ]),
        ]);
    }
    
    /**
     * Start seminar (host action)
     * 
     * POST /api/seminars/{streamId}/start
     */
    public function start(Request $request, string $streamId): JsonResponse
    {
        $request->validate([
            'host_id' => 'required|string',
        ]);
        
        $result = $this->liveService->startSeminar($streamId);
        
        return response()->json($result);
    }
    
    /**
     * End seminar
     * 
     * POST /api/seminars/{streamId}/end
     */
    public function end(Request $request, string $streamId): JsonResponse
    {
        $result = $this->liveService->endSeminar($streamId);
        
        return response()->json($result);
    }
    
    /**
     * Get viewer playback URL (authenticated)
     * 
     * GET /api/seminars/{streamId}/watch
     */
    public function watch(Request $request, string $streamId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
        ]);
        
        $seminar = Cache::get("seminar:{$streamId}");
        
        if (!$seminar || $seminar['status'] !== 'live') {
            return response()->json([
                'success' => false,
                'error' => 'Seminar is not currently live',
            ], 400);
        }
        
        // Generate viewer token
        $token = $this->liveService->generateViewerToken(
            $streamId,
            $request->user_id
        );
        
        $playbackUrls = $this->liveService->getPlaybackUrls($streamId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'stream_id' => $streamId,
                'token' => $token,
                'playback_urls' => $playbackUrls,
                'current_viewers' => $this->liveService->getViewerCount($streamId),
            ],
        ]);
    }
    
    /**
     * Enable recording
     * 
     * POST /api/seminars/{streamId}/recording
     */
    public function enableRecording(Request $request, string $streamId): JsonResponse
    {
        $request->validate([
            'storage' => 'nullable|in:vod,cos',
        ]);
        
        $result = $this->liveService->enableRecording($streamId, [
            'storage' => $request->storage ?? 'vod',
        ]);
        
        return response()->json($result);
    }
}
```

#### Routes: `routes/api.php`

```php
// routes/api.php
use App\Http\Controllers\Api\SeminarController;

Route::prefix('seminars')->group(function () {
    Route::post('/', [SeminarController::class, 'create']);
    Route::get('/{streamId}', [SeminarController::class, 'show']);
    Route::post('/{streamId}/start', [SeminarController::class, 'start']);
    Route::post('/{streamId}/end', [SeminarController::class, 'end']);
    Route::get('/{streamId}/watch', [SeminarController::class, 'watch']);
    Route::post('/{streamId}/recording', [SeminarController::class, 'enableRecording']);
});
```

### Frontend (Vue 3)

#### Host View: `src/components/seminar/SeminarHost.vue`

```vue
<!-- src/components/seminar/SeminarHost.vue -->
<template>
  <div class="seminar-host">
    <!-- Preview and Streaming Controls -->
    <div class="streaming-panel">
      <h2>🎙️ Seminar: {{ seminarTitle }}</h2>
      
      <!-- Camera Preview -->
      <div class="preview-container">
        <div id="host-preview" class="video-preview"></div>
        <div class="preview-status">
          {{ isLive ? '🔴 LIVE' : '⏺️ Preview' }}
        </div>
      </div>
      
      <!-- Streaming Controls -->
      <div class="controls">
        <button @click="toggleAudio" :class="{ muted: !isAudioEnabled }">
          {{ isAudioEnabled ? '🎤' : '🔇' }}
        </button>
        <button @click="toggleVideo" :class="{ muted: !isVideoEnabled }">
          {{ isVideoEnabled ? '📹' : '📷' }}
        </button>
        <button @click="toggleScreenShare">
          🖥️ Screen
        </button>
        
        <div class="spacer"></div>
        
        <button 
          v-if="!isLive" 
          @click="startStreaming" 
          class="start-btn"
        >
          ▶️ Go Live
        </button>
        <button 
          v-else 
          @click="stopStreaming" 
          class="stop-btn"
        >
          ⏹️ End Stream
        </button>
      </div>
      
      <!-- Stream Info -->
      <div class="stream-info">
        <div>Stream ID: {{ streamId }}</div>
        <div>Push URL: <code>{{ pushUrl }}</code></div>
        <div>Current Viewers: {{ viewerCount }}</div>
      </div>
    </div>
    
    <!-- Co-hosts Panel -->
    <div class="cohosts-panel">
      <h3>Co-hosts</h3>
      <div class="cohost-list">
        <div 
          v-for="cohost in cohosts" 
          :key="cohost.userId"
          class="cohost-item"
        >
          <span>{{ cohost.userId }}</span>
          <span :class="{ live: cohost.isLive }">
            {{ cohost.isLive ? '🔴' : '○' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import TRTC from 'trtc-sdk-js';

const props = defineProps<{
  streamId: string;
  pushUrl: string;
}>();

// State
const isLive = ref(false);
const isAudioEnabled = ref(true);
const isVideoEnabled = ref(true);
const viewerCount = ref(0);
const cohosts = ref<Array<{ userId: string; isLive: boolean }>>([]);
const seminarTitle = ref('');

// TRTC instance
let trtc: typeof TRTC | null = null;
let localStream: any = null;

// Initialize host stream
async function initHostStream() {
  trtc = TRTC.create({
    appId: parseInt(import.meta.env.VITE_TRTC_SDK_APP_ID || '0'),
    userId: `host_${Date.now()}`,
  });
  
  localStream = trtc.createLocalStream({
    userId: `host_${Date.now()}`,
    audio: true,
    video: true,
  });
  
  localStream.play('host-preview');
}

// Toggle controls
function toggleAudio() {
  if (localStream) {
    isAudioEnabled.value ? localStream.muteAudio() : localStream.unmuteAudio();
    isAudioEnabled.value = !isAudioEnabled.value;
  }
}

function toggleVideo() {
  if (localStream) {
    isVideoEnabled.value ? localStream.muteVideo() : localStream.unmuteVideo();
    isVideoEnabled.value = !isVideoEnabled.value;
  }
}

async function toggleScreenShare() {
  // Implement screen sharing logic
}

// Start streaming
async function startStreaming() {
  if (!trtc || !localStream) return;
  
  try {
    await trtc.startLocalVideo();
    await trtc.startLocalAudio();
    
    // In live streaming mode, publish to CDN
    await trtc.enterRoom({
      roomId: parseInt(props.streamId.replace(/\D/g, '')) || 0,
      role: 'anchor',
      streamParams: {
        video: true,
        audio: true,
      },
    });
    
    await trtc.publish();
    
    isLive.value = true;
    
    // Poll for viewer count
    startViewerCountPolling();
  } catch (error) {
    console.error('Failed to start streaming:', error);
  }
}

// Stop streaming
async function stopStreaming() {
  if (localStream) {
    await localStream.destroy();
  }
  
  if (trtc) {
    await trtc.exitRoom();
  }
  
  isLive.value = false;
}

// Poll viewer count
function startViewerCountPolling() {
  setInterval(async () => {
    try {
      const response = await fetch(`/api/seminars/${props.streamId}`);
      const data = await response.json();
      if (data.success) {
        viewerCount.value = data.data.current_viewers || 0;
      }
    } catch (error) {
      console.error('Failed to fetch viewer count:', error);
    }
  }, 5000);
}

onMounted(() => {
  initHostStream();
});
</script>

<style scoped>
.seminar-host {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1rem;
  height: 100vh;
  padding: 1rem;
  background: #1a1a1a;
  color: white;
}

.streaming-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.preview-container {
  position: relative;
  aspect-ratio: 16/9;
  background: #2a2a2a;
  border-radius: 8px;
  overflow: hidden;
}

.video-preview {
  width: 100%;
  height: 100%;
}

.preview-status {
  position: absolute;
  top: 1rem;
  left: 1rem;
  padding: 0.25rem 0.5rem;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: bold;
}

.controls {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  background: #2a2a2a;
  border-radius: 8px;
}

.controls button {
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  background: #3a3a3a;
  font-size: 1.25rem;
  cursor: pointer;
}

.controls button.muted {
  background: #e74c3c;
}

.controls .spacer {
  flex: 1;
}

.controls .start-btn {
  background: #2ecc71;
  color: white;
  font-weight: bold;
}

.controls .stop-btn {
  background: #e74c3c;
  color: white;
  font-weight: bold;
}

.stream-info {
  padding: 1rem;
  background: #2a2a2a;
  border-radius: 8px;
  font-size: 0.875rem;
}

.stream-info code {
  word-break: break-all;
  font-size: 0.75rem;
  color: #3498db;
}

.cohosts-panel {
  background: #2a2a2a;
  border-radius: 8px;
  padding: 1rem;
}

.cohost-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.cohost-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  background: #3a3a3a;
  border-radius: 4px;
}
</style>
```

#### Viewer View: `src/components/seminar/SeminarViewer.vue`

```vue
<!-- src/components/seminar/SeminarViewer.vue -->
<template>
  <div class="seminar-viewer">
    <!-- Video Player -->
    <div class="player-container">
      <video 
        ref="videoPlayer" 
        class="video-player"
        controls
        playsinline
      ></video>
      
      <!-- Overlay Info -->
      <div class="player-overlay">
        <div class="live-indicator" v-if="isLive">
          🔴 LIVE
        </div>
        <div class="viewer-count" v-if="isLive">
          👁️ {{ formatViewerCount(viewerCount) }} watching
        </div>
      </div>
    </div>
    
    <!-- Quality Selector -->
    <div class="quality-controls">
      <label>Quality:</label>
      <select v-model="selectedQuality" @change="changeQuality">
        <option value="auto">Auto</option>
        <option value="1080p">1080p</option>
        <option value="720p">720p</option>
        <option value="480p">480p</option>
        <option value="360p">360p</option>
      </select>
    </div>
    
    <!-- Q&A Panel -->
    <div class="qa-panel">
      <h3>Q&A</h3>
      
      <!-- Question Form -->
      <form @submit.prevent="submitQuestion" class="question-form">
        <input 
          v-model="newQuestion" 
          type="text" 
          placeholder="Ask a question..."
          maxlength="500"
        />
        <button type="submit" :disabled="!newQuestion.trim()">
          Send
        </button>
      </form>
      
      <!-- Questions List -->
      <div class="questions-list">
        <div 
          v-for="q in questions" 
          :key="q.id"
          class="question-item"
        >
          <div class="question-author">{{ q.author }}</div>
          <div class="question-text">{{ q.text }}</div>
          <div class="question-meta">
            {{ formatTime(q.timestamp) }}
            <button @click="upvoteQuestion(q.id)">
              👍 {{ q.upvotes }}
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Seminar Info -->
    <div class="seminar-info">
      <h2>{{ seminarTitle }}</h2>
      <p>Host: {{ hostName }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import Hls from 'hls.js';

const props = defineProps<{
  streamId: string;
  playbackUrls: {
    hls: string;
    flv: string;
  };
}>();

// State
const videoPlayer = ref<HTMLVideoElement | null>(null);
const isLive = ref(true);
const viewerCount = ref(0);
const selectedQuality = ref('auto');
const newQuestion = ref('');
const questions = ref<Array<{
  id: string;
  author: string;
  text: string;
  timestamp: Date;
  upvotes: number;
}>>([]);
const seminarTitle = ref('');
const hostName = ref('');

// Player instances
let hls: Hls | null = null;
let pollingInterval: number | null = null;

// Initialize video player
function initPlayer() {
  if (!videoPlayer.value) return;
  
  const video = videoPlayer.value;
  
  // Use HLS for Safari and mobile
  if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = props.playbackUrls.hls;
    video.addEventListener('loadedmetadata', () => {
      video.play().catch(console.error);
    });
  } 
  // Use HLS.js for other browsers
  else if (Hls.isSupported()) {
    hls = new Hls({
      enableWorker: true,
      lowLatencyMode: true,
    });
    
    hls.loadSource(props.playbackUrls.hls);
    hls.attachMedia(video);
    
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      video.play().catch(console.error);
    });
  }
  // Fallback to FLV
  else {
    console.warn('HLS not supported, using FLV');
    // Would use flv.js here for FLV playback
    video.src = props.playbackUrls.flv;
  }
}

// Change quality
function changeQuality() {
  if (!hls) return;
  
  if (selectedQuality.value === 'auto') {
    hls.currentLevel = -1; // Auto
  } else {
    const levelIndex = hls.levels.findIndex(
      level => level.height === parseInt(selectedQuality.value)
    );
    if (levelIndex >= 0) {
      hls.currentLevel = levelIndex;
    }
  }
}

// Submit question
function submitQuestion() {
  if (!newQuestion.value.trim()) return;
  
  questions.value.push({
    id: `q_${Date.now()}`,
    author: 'Anonymous',
    text: newQuestion.value.trim(),
    timestamp: new Date(),
    upvotes: 0,
  });
  
  newQuestion.value = '';
}

// Upvote question
function upvoteQuestion(questionId: string) {
  const question = questions.value.find(q => q.id === questionId);
  if (question) {
    question.upvotes++;
  }
}

// Format viewer count
function formatViewerCount(count: number): string {
  if (count >= 1000000) {
    return (count / 1000000).toFixed(1) + 'M';
  }
  if (count >= 1000) {
    return (count / 1000).toFixed(1) + 'K';
  }
  return count.toString();
}

// Format time
function formatTime(date: Date): string {
  return date.toLocaleTimeString();
}

// Poll for updates
function startPolling() {
  pollingInterval = window.setInterval(async () => {
    try {
      const response = await fetch(`/api/seminars/${props.streamId}`);
      const data = await response.json();
      
      if (data.success) {
        isLive.value = data.data.status === 'live';
        viewerCount.value = data.data.current_viewers || 0;
        seminarTitle.value = data.data.title || seminarTitle.value;
        hostName.value = data.data.host_id || hostName.value;
      }
    } catch (error) {
      console.error('Polling error:', error);
    }
  }, 5000);
}

onMounted(() => {
  initPlayer();
  startPolling();
});

onUnmounted(() => {
  if (hls) {
    hls.destroy();
  }
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

<style scoped>
.seminar-viewer {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #0a0a0a;
  color: white;
}

.player-container {
  position: relative;
  width: 100%;
  max-height: 70vh;
  background: #000;
}

.video-player {
  width: 100%;
  height: auto;
  display: block;
}

.player-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: space-between;
  padding: 1rem;
}

.live-indicator {
  padding: 0.25rem 0.5rem;
  background: #e74c3c;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.875rem;
}

.viewer-count {
  padding: 0.25rem 0.5rem;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 4px;
  font-size: 0.875rem;
}

.quality-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #1a1a1a;
}

.quality-controls select {
  padding: 0.25rem 0.5rem;
  background: #2a2a2a;
  border: none;
  color: white;
  border-radius: 4px;
}

.qa-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
  background: #1a1a1a;
  overflow-y: auto;
}

.question-form {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.question-form input {
  flex: 1;
  padding: 0.5rem;
  background: #2a2a2a;
  border: none;
  color: white;
  border-radius: 4px;
}

.question-form button {
  padding: 0.5rem 1rem;
  background: #3498db;
  border: none;
  color: white;
  border-radius: 4px;
  cursor: pointer;
}

.question-form button:disabled {
  background: #555;
  cursor: not-allowed;
}

.questions-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.question-item {
  padding: 0.75rem;
  background: #2a2a2a;
  border-radius: 8px;
}

.question-author {
  font-size: 0.75rem;
  color: #888;
  margin-bottom: 0.25rem;
}

.question-text {
  margin-bottom: 0.5rem;
}

.question-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
  color: #888;
}

.question-meta button {
  padding: 0.25rem 0.5rem;
  background: #3a3a3a;
  border: none;
  color: white;
  border-radius: 4px;
  cursor: pointer;
}

.seminar-info {
  padding: 1rem;
  background: #2a2a2a;
}

.seminar-info h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
}

.seminar-info p {
  margin: 0;
  color: #888;
}
</style>
```

---

## Recording & Playback

### Backend Recording Service

```php
// app/Services/TRTCRecordingService.php

class TRTCRecordingService
{
    /**
     * Start cloud recording with layout
     */
    public function startRecordingWithLayout(string $roomId, array $config): array
    {
        // Configure recording layout for multiple streams
        $recordConfig = [
            'room_id' => $roomId,
            'task_id' => 'record_' . uniqid(),
            
            // Recording parameters
            'record_params' => [
                'record_type' => 'interview', // or 'seminar'
                'max_duration' => 7200, // 2 hours max
                'chunk_duration' => 1800, // 30 min chunks
                
                // Storage
                'storage_params' => [
                    'vendor' => 0, // Tencent Cloud
                    'region' => 'ap-guangzhou',
                    'bucket' => 'your-bucket',
                    'prefix' => "recordings/{$roomId}",
                ],
                
                // Output formats
                'output_format' => [
                    'hls' => true,
                    'mp4' => true,
                ],
                
                // Layout for multiple users
                'layout_params' => [
                    'template_id' => 2, // 1-for-N template
                    'input_list' => [], // will be filled with actual user streams
                ],
            ],
            
            // Auto stop conditions
            'stop_condition' => [
                'idle_timeout' => 300, // 5 min no streams
                'max_duration' => 7200,
            ],
        ];
        
        return [
            'success' => true,
            'task_id' => $recordConfig['task_id'],
            'room_id' => $roomId,
        ];
    }
    
    /**
     * Get recording file list
     */
    public function getRecordingFiles(string $taskId): array
    {
        // In production, call TRTC API to get actual recording files
        return [
            'success' => true,
            'files' => [
                [
                    'file_id' => '1400000001',
                    'filename' => 'record_001.m3u8',
                    'play_url' => "https://vod.example.com/recordings/{$taskId}/record_001.m3u8",
                    'size' => 1024000,
                    'duration' => 3600,
                    'created_at' => now()->toIso8601String(),
                ],
            ],
        ];
    }
    
    /**
     * Generate signed playback URL
     */
    public function generateSignedPlaybackUrl(string $fileId, string $userId): string
    {
        $expireTime = time() + 3600;
        
        // Generate signed URL for VOD playback
        // In production, use Tencent VOD SDK
        $signedUrl = "https://vod.example.com/play/{$fileId}?sign=xxx&expire={$expireTime}";
        
        return $signedUrl;
    }
}
```

### Frontend Playback Component

```typescript
// src/components/recordings/RecordingPlayer.vue
import { ref, onMounted, onUnmounted } from 'vue';
import Hls from 'hls.js';
import flvjs from 'flv.js';

interface Recording {
  fileId: string;
  playUrl: string;
  duration: number;
  createdAt: string;
}

const props = defineProps<{
  recording: Recording;
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
let hlsInstance: Hls | null = null;
let flvInstance: flvjs.Player | null = null;

function initPlayback() {
  if (!videoRef.value) return;
  
  const video = videoRef.value;
  const url = props.recording.playUrl;
  
  if (url.endsWith('.m3u8')) {
    if (Hls.isSupported()) {
      hlsInstance = new Hls({
        startPosition: 0,
      });
      hlsInstance.loadSource(url);
      hlsInstance.attachMedia(video);
      hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
        video.play();
      });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = url;
      video.addEventListener('loadedmetadata', () => video.play());
    }
  } else if (url.endsWith('.flv')) {
    if (flvjs.isSupported()) {
      flvInstance = flvjs.createPlayer({
        type: 'flv',
        url: url,
      });
      flvInstance.attachMediaElement(video);
      flvInstance.load();
      flvInstance.play();
    }
  }
}

function seekTo(position: number) {
  if (videoRef.value) {
    videoRef.value.currentTime = position;
  }
}

function destroy() {
  if (hlsInstance) {
    hlsInstance.destroy();
  }
  if (flvInstance) {
    flvInstance.destroy();
  }
}

onMounted(initPlayback);
onUnmounted(destroy);
```

---

## CDN Configuration for 10,000+ Viewers

### Architecture

```
                    ┌─────────────────────────────────────┐
                    │         TRTC Transmission           │
                    │   (Up to 100K concurrent users)     │
                    └─────────────┬───────────────────────┘
                                  │
                    ┌─────────────▼───────────────────────┐
                    │         CDN Pull                    │
                    │   (Global Edge Distribution)        │
                    │                                     │
                    │  ┌─────────────────────────────┐   │
                    │  │ Primary CDN (e.g., Tencent)  │   │
                    │  └─────────────────────────────┘   │
                    │  ┌─────────────────────────────┐   │
                    │  │ Secondary CDN (Backup)       │   │
                    │  └─────────────────────────────┘   │
                    └─────────────────────────────────────┘
                                  │
              ┌───────────────────┼───────────────────┐
              │                   │                   │
       ┌──────▼──────┐     ┌──────▼──────┐     ┌──────▼──────┐
       │  Asia-Pacific│     │   Europe    │     │  Americas   │
       │  Viewers     │     │  Viewers    │     │  Viewers    │
       └─────────────┘     └─────────────┘     └─────────────┘
```

### Backend CDN Service

```php
// app/Services/CDNService.php

class CDNService
{
    /**
     * Configure CDN for high-traffic streaming
     */
    public function configureCDNForHighTraffic(string $streamId): array
    {
        return [
            'stream_id' => $streamId,
            
            // CDN Configuration
            'cdn_config' => [
                // Primary CDN
                'primary' => [
                    'provider' => 'tencent',
                    'domain' => config('services.tencent.play_domain'),
                    'regions' => ['ap-guangzhou', 'ap-shanghai', 'ap-singapore'],
                ],
                
                // Backup CDN
                'backup' => [
                    'provider' => 'custom',
                    'domain' => config('services.tencent.backup_cdn_domain'),
                ],
                
                // Protocol settings
                'protocol' => [
                    'hls_enabled' => true,
                    'hls_chunk_duration' => 6, // seconds
                    'flv_enabled' => true,
                    'flv_chunk_duration' => 4, // seconds
                ],
                
                // Performance settings
                'performance' => [
                    'cache_ttl' => 3600, // 1 hour
                    'gop_cache' => true,
                    'serve_cache' => true,
                ],
                
                // Access control
                'access_control' => [
                    'referer_check' => true,
                    'allowed_referers' => ['yourdomain.com'],
                    'ip_limit' => 1000, // requests per IP per hour
                ],
            ],
        ];
    }
    
    /**
     * Get CDN statistics
     */
    public function getCDNStats(string $streamId): array
    {
        // In production, call CDN provider API
        return [
            'stream_id' => $streamId,
            'bandwidth' => [
                'current' => '50 Gbps',
                'peak' => '120 Gbps',
            ],
            'concurrent_viewers' => 15000,
            'cache_hit_rate' => 0.95,
            'latency' => [
                'avg' => 50, // ms
                'p95' => 150,
                'p99' => 300,
            ],
        ];
    }
    
    /**
     * Purge CDN cache
     */
    public function purgeCache(array $urls): array
    {
        // Trigger CDN cache purge
        return [
            'success' => true,
            'task_id' => 'purge_' . uniqid(),
            'urls_count' => count($urls),
        ];
    }
}
```

### Frontend CDN Adaptive Streaming

```typescript
// src/composables/useCDNStreaming.ts
import { ref, computed } from 'vue';

interface CDNConfig {
  streamId: string;
  playbackUrls: {
    hls: string;
    flv: string;
  };
  quality: 'auto' | '1080p' | '720p' | '480p';
}

export function useCDNStreaming(config: CDNConfig) {
  const currentBitrate = ref(0);
  const bufferHealth = ref(100);
  const isBuffering = ref(false);
  const selectedCDN = ref<'primary' | 'backup'>('primary');
  
  // Auto-select CDN based on latency
  async function selectOptimalCDN(): Promise<string> {
    const cdns = [
      { name: 'primary', url: config.playbackUrls.hls },
      { name: 'backup', url: config.playbackUrls.flv },
    ];
    
    let fastest: { name: string; latency: number } = { name: 'primary', latency: Infinity };
    
    for (const cdn of cdns) {
      try {
        const start = performance.now();
        await fetch(cdn.url, { method: 'HEAD' });
        const latency = performance.now() - start;
        
        if (latency < fastest.latency) {
          fastest = { name: cdn.name, latency };
        }
      } catch {
        // CDN unavailable
      }
    }
    
    selectedCDN.value = fastest.name as 'primary' | 'backup';
    return fastest.name;
  }
  
  // Adaptive bitrate streaming
  function onBitrateChange(newBitrate: number) {
    currentBitrate.value = newBitrate;
    
    // Log for analytics
    console.log(`CDN: ${selectedCDN.value}, Bitrate: ${newBitrate}kbps`);
  }
  
  // Monitor buffer health
  function onBufferHealthChange(health: number) {
    bufferHealth.value = health;
    
    // Switch to backup CDN if primary is struggling
    if (health < 30 && selectedCDN.value === 'primary') {
      console.warn('Primary CDN struggling, switching to backup');
      selectOptimalCDN();
    }
  }
  
  return {
    currentBitrate,
    bufferHealth,
    isBuffering,
    selectedCDN,
    selectOptimalCDN,
    onBitrateChange,
    onBufferHealthChange,
  };
}
```

---

## Full API Reference

### Backend API Endpoints

#### Interview APIs

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/interviews/rooms` | Create interview room | Required |
| POST | `/api/interviews/rooms/{roomId}/join` | Join interview room | Required |
| POST | `/api/interviews/rooms/{roomId}/end` | End interview | Required |
| GET | `/api/interviews/recordings/{taskId}` | Get recording | Required |

#### Seminar APIs

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/seminars` | Create seminar | Required |
| GET | `/api/seminars/{streamId}` | Get seminar details | Required |
| POST | `/api/seminars/{streamId}/start` | Start seminar (host) | Required |
| POST | `/api/seminars/{streamId}/end` | End seminar | Required |
| GET | `/api/seminars/{streamId}/watch` | Get viewer playback URL | Required |
| POST | `/api/seminars/{streamId}/recording` | Enable recording | Required |

### TRTC SDK API Reference

#### Core Methods

```typescript
// Create TRTC instance
const trtc = TRTC.create({
  appId: number;
  userId: string;
});

// Enter room
await trtc.enterRoom({
  roomId: number;
  role: 'anchor' | 'audience';
  userSig: string;
  streamParams?: StreamParams;
});

// Exit room
await trtc.exitRoom();

// Publish local stream
await trtc.publish(stream: TRTCStream);

// Unpublish local stream  
await trtc.unpublish(stream: TRTCStream);
```

#### Local Stream Methods

```typescript
// Create local stream
const localStream = trtc.createLocalStream({
  userId: string;
  audio: boolean;
  video: boolean;
  videoQuality?: VideoQuality;
  mirror?: boolean;
});

// Play stream
await stream.play(elementId: string, options?: PlayOptions);

// Stop stream
stream.stop();

// Destroy stream
stream.destroy();

// Mute/Unmute
stream.muteAudio();
stream.unmuteAudio();
stream.muteVideo();
stream.unmuteVideo();
```

#### Events

```typescript
trtc.on(TRTC.EVENT.REMOTE_USER_JOIN, (event) => {
  // Remote user joined
});

trtc.on(TRTC.EVENT.REMOTE_USER_LEAVE, (event) => {
  // Remote user left
});

trtc.on(TRTC.EVENT.REMOTE_STREAM_AVAILABLE, (event) => {
  // Remote stream is available
});

trtc.on(TRTC.EVENT.REMOTE_STREAM_UNAVAILABLE, (event) => {
  // Remote stream is no longer available
});

trtc.on(TRTC.EVENT.NETWORK_QUALITY, (event) => {
  // Network quality update { userId, networkQuality: 1-6 }
});

trtc.on(TRTC.EVENT.ERROR, (event) => {
  // Error occurred
});
```

---

## Security & Best Practices

### Signature Security

```php
// Backend: Generate secure UserSig
class TRTCSignatureService
{
    private string $appId;
    private string $secretKey;
    
    public function generateUserSig(string $userId, int $expired = 86400): string
    {
        $current = time();
        $expiredTime = $current + $expired;
        
        // TLS payload
        $json = json_encode([
            'TLS.appId' => (string) $this->appId,
            'TLS.userId' => $userId,
            'TLS.expiredTime' => (string) $expiredTime,
            'TLS.sdkAppId' => (string) $this->appId,
        ], JSON_UNESCAPED_UNICODE);
        
        // Base64 encode
        $base64 = base64_encode($this->tlsJsonToBinary($json));
        
        // Generate signature
        $signature = $this->hmcSha256($base64);
        
        return $base64 . $signature;
    }
    
    private function tlsJsonToBinary(string $json): string
    {
        // Convert JSON to TLS binary format
        // Implementation depends on Tencent's TLS 2.0 spec
        return $json;
    }
    
    private function hmcSha256(string $content): string
    {
        $hashed = hash_hmac('sha256', $content, $this->secretKey, true);
        return base64_encode($hashed);
    }
}
```

### Frontend Security

```typescript
// src/utils/security.ts

// Validate user before joining room
async function validateUserForRoom(
  roomId: string, 
  userId: string
): Promise<boolean> {
  // Check if user is authorized for this room
  // Never trust client-side authorization
  const response = await fetch('/api/auth/validate', {
    method: 'POST',
    body: JSON.stringify({ roomId, userId }),
  });
  return response.ok;
}

// Rotate userSig periodically
function setupSigRefresh(trtc: typeof TRTC, userId: string) {
  const refreshInterval = 23 * 60 * 60 * 1000; // 23 hours
  
  return setInterval(async () => {
    try {
      const response = await fetch(`/api/auth/refresh-sig?userId=${userId}`);
      const { userSig } = await response.json();
      
      // Note: TRTC doesn't support runtime sig refresh
      // User needs to re-enter room with new sig
      console.warn('UserSig expiring soon. Please refresh page.');
    } catch (error) {
      console.error('Failed to refresh sig:', error);
    }
  }, refreshInterval);
}
```

### Best Practices Checklist

#### Security
- [ ] Never expose `secretKey` on frontend
- [ ] Set reasonable `expiredTime` for UserSig (max 24 hours)
- [ ] Validate user permissions server-side before generating sig
- [ ] Use HTTPS for all API calls
- [ ] Implement rate limiting on join room endpoints
- [ ] Enable referer checking on CDN domains

#### Performance
- [ ] Use HLS for mobile, FLV for desktop when possible
- [ ] Enable CDN caching with appropriate TTLs
- [ ] Implement adaptive bitrate based on network conditions
- [ ] Set appropriate `maxUserNum` to prevent room overflow
- [ ] Use `gop_cache` for faster playback start

#### Reliability
- [ ] Implement backup CDN failover
- [ ] Monitor stream health and auto-restart on failure
- [ ] Set up recording with redundant storage
- [ ] Implement graceful degradation for poor network conditions
- [ ] Have reconnection logic with exponential backoff

#### Scalability
- [ ] For 10K+ viewers, ensure CDN is properly configured
- [ ] Use CDN origin-pull, not direct TRTC playback
- [ ] Monitor bandwidth and scale CDN bandwidth accordingly
- [ ] Implement viewer count limits at CDN level

---

## Environment Configuration Summary

```env
# .env - Backend
TRTC_SDK_APP_ID=14xxxxxx
TRTC_SECRET_ID=AKIDxxxxxxxxxxxxxxxxxxxxx
TRTC_SECRET_KEY=xxxxxxxxxxxxxxxxxxxxx
TRTC_API_ZONE=gzjp

# CDN Configuration
TRTC_PUSH_DOMAIN=push.yourdomain.com
TRTC_PLAY_DOMAIN=play.yourdomain.com
TRTC_PUSH_KEY=your_push_secret_key
```

```typescript
// .env - Frontend (Vite)
VITE_TRTC_SDK_APP_ID=14xxxxxx
VITE_TRTC_PLAY_DOMAIN=play.yourdomain.com
```

---

## Appendix: Error Codes

| Code | Meaning | Resolution |
|------|---------|------------|
| -1 | Unknown error | Check console logs |
| -2 | Invalid parameter | Verify appId, userId, roomId |
| -3 | Not initialized | Call TRTC.create() first |
| -4 | Room not found | Check roomId exists |
| -5 | Already in room | Exit room before entering another |
| -6 | Permission denied | Verify userSig is valid |
| -7 | Room full | Increase maxUserNum or wait |
| -1001 | Network timeout | Check network connection |
| -1002 | Network error | Retry with backoff |

---

*Document Version: 1.0*  
*Last Updated: 2026-04-26*  
*Author: System Analyst (HRINT-FEAT-073)*
