<x-mail::message>
# Website enquiry

**Name:** {{ $enquiry->name }}<br>
**Email:** {{ $enquiry->email }}<br>
**Subject:** {{ $enquiry->subject }}

{!! nl2br(e($enquiry->message)) !!}

Reply to this email to answer {{ $enquiry->name }} directly.
</x-mail::message>
