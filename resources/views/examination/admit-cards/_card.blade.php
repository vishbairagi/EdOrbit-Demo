@php
    $studentSchool = $card->student->school ?? $school ?? $exam->school;
    $session = $exam->session ?? \App\Models\AcademicSession::find($exam->academic_session_id) ?? \App\Models\AcademicSession::find(session('selected_academic_session_id'));
    
    // Active subjects or all exam papers
    $activeSubjectIds = collect($card->student->activeSubjects ?? [])->pluck('id')->toArray();
    $studentPapers = !empty($activeSubjectIds)
        ? collect($papers)->filter(fn($p) => in_array($p->subject_id, $activeSubjectIds))
        : collect($papers);

    // Signature resolution
    $schoolSig = \App\Models\SchoolSetting::getSetting($studentSchool->id, $session?->id, 'school_signature');
    $examSig = (($exam->settings['teacher_signature_enabled'] ?? false) && !empty($exam->settings['teacher_signature_path'])) 
        ? 'storage/'.$exam->settings['teacher_signature_path'] 
        : null;
    $signaturePath = $examSig ?: ($schoolSig ? 'storage/'.$schoolSig : null);
@endphp

<div id="card-{{ $card->id }}" class="admit-card">
    <div class="admit-card-inner">
        {{-- 1. School Header --}}
        <div class="card-school-header">
            <h1 class="school-name">{{ $studentSchool->name ?? 'School Name' }}</h1>
            <div class="school-contact">
                Phone: {{ $studentSchool->phone ?? '—' }} &nbsp;&nbsp;|&nbsp;&nbsp; Email: {{ $studentSchool->email ?? '—' }}
            </div>
            <div class="school-address">
                Address: {{ $studentSchool->address ?? '—' }}
            </div>
            <div class="header-divider"></div>
        </div>

        {{-- 2. Card Title --}}
        <div class="card-title-box">
            <span class="card-title">STUDENT ADMIT CARD</span>
        </div>

        {{-- 3. Student Details + Photo --}}
        <div class="student-details-wrapper">
            <div class="student-info-col">
                <div class="info-row">
                    <span class="info-label">Student Name:</span>
                    <span class="info-val">{{ $card->student->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Session:</span>
                    <span class="info-val">{{ $session->name ?? '—' }}</span>
                </div>
                <div class="info-row info-row-multi">
                    <div class="info-group">
                        <span class="info-label">Class:</span>
                        <span class="info-val">{{ $card->student->schoolClass->name }}</span>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Section:</span>
                        <span class="info-val">{{ $card->student->section?->name ?? 'A' }}</span>
                    </div>
                    <div class="info-group roll-group">
                        <span class="info-label">Exam Roll Number:</span>
                        <span class="info-val font-mono">{{ $card->exam_roll_number }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-val">{{ $card->student->email ?? '—' }}</span>
                </div>
            </div>

            <div class="student-photo-box">
                @if($card->student->photo_url)
                    <img src="{{ $card->student->photo_url }}" alt="{{ $card->student->full_name }}" class="student-photo-img">
                @else
                    <div class="student-photo-placeholder">
                        <svg class="placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Photo</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- 4. Exam Info Line --}}
        <div class="exam-info-line">
            <span class="exam-info-item"><strong>Exam:</strong> {{ $exam->title }} ({{ $exam->start_date?->format('d-m-Y') }} - {{ $exam->end_date?->format('d-m-Y') }})</span>
            <span class="exam-info-item"><strong>Class:</strong> {{ $card->student->schoolClass->name }}</span>
        </div>

        {{-- 5. Exam Papers Schedule Table --}}
        <table class="papers-schedule-table no-theme-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Subject</th>
                    <th style="width: 14%;">Type</th>
                    <th style="width: 15%;">Paper Code</th>
                    <th style="width: 16%;">Date</th>
                    <th style="width: 21%;">Timing</th>
                    <th style="width: 12%;">Room No.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentPapers as $paper)
                    <tr>
                        <td>{{ $paper->subject_name }}</td>
                        <td>{{ ucfirst($paper->subject_type ?? 'Theory') }}</td>
                        <td>{{ $paper->paper_code ?? '—' }}</td>
                        <td>{{ $paper->paper_date ? $paper->paper_date->format('d-m-Y') : '—' }}</td>
                        <td>
                            {{ $paper->start_time ? \Carbon\Carbon::parse($paper->start_time)->format('h:i a') : '—' }} - {{ $paper->end_time ? \Carbon\Carbon::parse($paper->end_time)->format('h:i a') : '—' }}
                        </td>
                        <td>{{ $paper->room_number ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #000; padding: 10px;">No scheduled subjects found for this exam.</td>
                    </tr>
                @endforelse
                <tr>
                    <td class="exam-center-label">Exam Center</td>
                    <td colspan="5" class="exam-center-value">{{ $exam->exam_center ?? 'Main Campus' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 6. Authorized Signature Footer --}}
        <div class="card-footer-signature">
            <div class="signature-box">
                <div class="signature-display">
                    @if($signaturePath && file_exists(public_path($signaturePath)))
                        <img src="{{ asset($signaturePath) }}" alt="Signature" class="signature-img">
                    @else
                        <span class="signature-script">{{ $studentSchool->name ?? 'Intechno school' }}</span>
                    @endif
                </div>
                <div class="signature-divider"></div>
                <div class="signature-title">Authorized By</div>
            </div>
        </div>
    </div>
</div>
