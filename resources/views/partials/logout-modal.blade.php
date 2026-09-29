<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3" style="border-radius:24px">
            <p class="fw-semibold mt-3">Are you sure you want<br>to Log Out?</p>
            <form method="POST" action="{{ route('logout') }}" class="d-flex justify-content-center gap-2 mb-2">
                @csrf
                <button type="button" class="btn btn-soft px-4" data-bs-dismiss="modal">No</button>
                <button type="submit" class="btn btn-navy px-4">Yes</button>
            </form>
        </div>
    </div>
</div>