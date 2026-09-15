

const spine = document.getElementById("storySpine"),
    sections = [...document.querySelectorAll(".story-section")];
let frame = 0;
function progress() {
    if (frame) return;
    frame = requestAnimationFrame(() => {
        const spineRect = spine.getBoundingClientRect(),
            lastNode = sections[sections.length - 1]
                .querySelector(".story-node")
                .getBoundingClientRect(),
            target = Math.max(
                1,
                Math.min(
                    100,
                    ((lastNode.top + lastNode.height / 2 - spineRect.top) /
                        spineRect.height) *
                        100,
                ),
            ),
            current = Math.max(
                0,
                ((innerHeight * 0.56 - spineRect.top) / spineRect.height) * 100,
            );
        spine.style.setProperty(
            "--story-progress",
            Math.min(current, target) + "%",
        );
        frame = 0;
    });
}
addEventListener("scroll", progress, { passive: true });
addEventListener("resize", progress);
const observer = new IntersectionObserver(
    (es) =>
        es.forEach(
            (e) => e.isIntersecting && e.target.classList.add("is-active"),
        ),
    { rootMargin: "-12% 0px -38% 0px", threshold: 0.1 },
);
sections.forEach((s) => observer.observe(s));
progress();
