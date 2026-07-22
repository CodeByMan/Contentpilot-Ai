@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner"><div class="nk-content-body">
        <div class="nk-block-head nk-page-head"><div class="nk-block-head-content">
            <h2 class="display-6">Contact Messages</h2><p class="text-muted">Messages submitted through the public contact form.</p>
        </div></div>
        <div class="card"><div class="table-responsive"><table class="table table-middle mb-0">
            <thead class="table-light"><tr><th>Received</th><th>Sender</th><th>Subject</th><th>Message</th><th>Action</th></tr></thead>
            <tbody>
                @forelse ($contacts as $contact)
                    <tr>
                        <td class="text-nowrap">{{ $contact->created_at->format('M j, Y H:i') }}</td>
                        <td><strong>{{ $contact->name }}</strong><br><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                        <td>{{ $contact->subject }}</td>
                        <td class="contact-message-cell">{{ $contact->message }}</td>
                        <td><x-inline-delete-button :action="route('admin.contacts.destroy', $contact)" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No contact messages found.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
        <div class="mt-3">{{ $contacts->links() }}</div>
    </div></div>
@endsection
