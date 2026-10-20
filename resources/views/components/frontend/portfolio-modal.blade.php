<div class="modal fade portfolio-modal" id="detailModal" tabindex="-1" aria-labelledby="detailTitle" aria-describedby="detailDescription" data-portfolio-modal>
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <article class="modal-content professional-portfolio">
            <button class="portfolio-close" type="button" data-bs-dismiss="modal" aria-label="Tutup profil"><span aria-hidden="true">&times;</span></button>
            <div class="portfolio-visual">
                <img data-portfolio-photo alt="" width="720" height="1000" hidden>
                <div class="portfolio-monogram" data-portfolio-placeholder aria-hidden="true"></div>
                <div class="portfolio-identity">
                    <span class="portfolio-firm">HS &amp; CO <span>COUNSELLORS AT LAW</span></span>
                    <p data-portfolio-role></p>
                    <h2 id="detailTitle"></h2>
                    <p class="portfolio-focus" data-portfolio-focus></p>
                </div>
            </div>
            <div class="portfolio-content">
                <div class="portfolio-top"><span>Portofolio profesional</span><a href="{{ route('contact') }}#enquiry" data-portfolio-contact>Hubungi tim <span aria-hidden="true">&#8599;</span></a></div>
                <section class="portfolio-block"><h3>Profil</h3><div id="detailDescription" class="portfolio-biography"></div></section>
                <section class="portfolio-block" data-portfolio-practices-block hidden><h3>Fokus keahlian</h3><div class="portfolio-tags" data-portfolio-tags></div></section>
                <section class="portfolio-block" data-portfolio-qualifications-block hidden><h3>Kualifikasi</h3><ul data-portfolio-qualifications></ul></section>
                <section class="portfolio-block" data-portfolio-languages-block hidden><h3>Bahasa</h3><div class="portfolio-tags" data-portfolio-languages></div></section>
                <p class="portfolio-note">Untuk konsultasi atau informasi lebih lanjut, silakan hubungi tim HS &amp; CO.</p>
            </div>
        </article>
    </div>
</div>