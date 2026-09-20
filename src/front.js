import './front.scss';

const containerSelector  = '.infinite-scroll-container';
const triggerSelector      = '.infinite-scroll-trigger';
const buttonSelector       = '.infinite-scroll-button';

const more = trigger => {
	const node      = document.createElement('div')
	const container = trigger.closest(containerSelector)
	const url       = `/wp-admin/admin-ajax.php?action=load_more&page=${container.getAttribute('data-page')}&block=${container.getAttribute('data-block-key')}&query=${container.getAttribute('data-query-key')}`;

	const placeholderNodes = []

	const template = container.querySelector('template')
	if ( template ) {
		placeholderNodes.push(...Array.from(template.content.childNodes))
		container.replaceWith(...placeholderNodes)
	} else {
		placeholderNodes.push(placeholder)
	}

	fetch( url ).then( async response => {
		node.innerHTML = await response.text()
		placeholderNodes[0].replaceWith( ...node.querySelectorAll('ul>li') )
		placeholderNodes.map(el=>el.remove())
		// apply styles
		document.querySelector('#block-style-variation-styles-inline-css').innerText += node.querySelector('style').innerText;
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
		entry.target.querySelectorAll(triggerSelector).forEach( observeTrigger )
	} )
} );
domObserver.observe( document.body, { subtree: true, childList: true } );


document.addEventListener('click',e => {
	if ( e.target.closest('button')?.matches(buttonSelector) ) {
		more(e.target.closest('button'))
	}
})
