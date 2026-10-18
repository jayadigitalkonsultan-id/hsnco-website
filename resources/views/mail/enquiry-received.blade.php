<h1>Permintaan konsultasi baru</h1>
<p>Permintaan baru telah tersimpan di inbox admin HSNCO.</p>
<dl>
    <dt>Nama</dt><dd>{{ $enquiry->first_name }} {{ $enquiry->last_name }}</dd>
    <dt>Email</dt><dd>{{ $enquiry->email }}</dd>
    @if($enquiry->phone)<dt>Telepon</dt><dd>{{ $enquiry->phone }}</dd>@endif
    <dt>Bidang bantuan</dt><dd>{{ $enquiry->area_label }}</dd>
    <dt>Sumber</dt><dd>{{ $enquiry->source }}</dd>
    <dt>Pesan</dt><dd>{{ $enquiry->message }}</dd>
</dl>
<p><a href="{{ route('admin.enquiries.show', $enquiry) }}">Buka permintaan di admin</a></p>
