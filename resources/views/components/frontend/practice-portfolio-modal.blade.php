<div class="modal fade practice-modal" id="practiceModal" tabindex="-1" aria-labelledby="practiceModalTitle" aria-describedby="practiceModalOverview">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <article class="modal-content practice-portfolio">
            <button type="button" class="practice-modal-close" data-bs-dismiss="modal" aria-label="Tutup bidang praktik"><span aria-hidden="true">&times;</span></button>
            <div class="practice-portfolio-visual">
                <img data-practice-modal-image alt="" width="720" height="1000">
                <div class="practice-portfolio-identity"><span data-practice-modal-number></span><h2 id="practiceModalTitle"></h2><p>HS &amp; CO / Bidang Praktik</p><small>Ilustrasi bidang layanan</small></div>
            </div>
            <div class="practice-portfolio-content">
                <p class="practice-portfolio-eyebrow">Layanan hukum / Indonesia</p>
                <section class="practice-portfolio-block" data-practice-overview-block><h3>Gambaran layanan</h3><p id="practiceModalOverview"></p></section>
                <section class="practice-portfolio-block" data-practice-services-block><h3>Lingkup layanan</h3><ul data-practice-modal-services></ul></section>
                <section class="practice-portfolio-block"><h3>Pendampingan profesional</h3><p>Kenali tim HS &amp; CO untuk mendiskusikan kebutuhan hukum Anda.</p><a class="practice-team-link" href="{{ route('team') }}">Tim profesional <span aria-hidden="true">&rarr;</span></a></section>
                <a class="practice-consultation-link" href="{{ route('contact') }}#enquiry">Diskusikan kebutuhan Anda <span aria-hidden="true">&#8599;</span></a>
            </div>
        </article>
    </div>
</div>