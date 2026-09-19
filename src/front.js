import './front.scss';

const triggerSelector = '.infinite-scroll-trigger';

const more = trigger => {
	console.log(trigger)
	const node = document.createElement('div')
	const url  = `/wp-admin/admin-ajax.php?action=load_more&page=${trigger.getAttribute('data-page')}&block=${trigger.getAttribute('data-block-key')}&query=${trigger.getAttribute('data-query-key')}`;

	// TODO add spinner

	fetch( url ).then( async response => {
		node.innerHTML = await response.text()
		trigger.replaceWith( ...node.querySelectorAll('ul>li') )
	})
}

const observeTrigger = trigger => {
	trigger.setAttribute('data-observed',true)
	moreObserver.observe(trigger)
}

const moreObserver = new IntersectionObserver(
	(entries,observer) => {
		console.log(entries)
		entries.forEach( entry => {

			if ( entry.isIntersecting ) {

				observer.unobserve(entry.target)

				more( entry.target );

			}
		});
	},
	{
		root: null,
		rootMargin: '15px',
		threshold: 1.0
	}
);
document.querySelectorAll(triggerSelector).forEach( observeTrigger )

const domObserver = new MutationObserver( ( mutations, domObserver ) => {
	Array.from(mutations).forEach( entry => {

		// endless scroll observer
		entry.target.querySelectorAll(triggerSelector).forEach( observeTrigger )
	} )
} );
domObserver.observe( document.body, { subtree: true, childList: true } );
