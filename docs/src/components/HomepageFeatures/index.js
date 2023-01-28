import React from 'react';
import clsx from 'clsx';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: 'Easy would be to easy',
    Svg: require('@site/static/img/26A1.svg').default,
    description: (
      <>
        The new design concept is to get an easy setup for new developers,
        and the website should run as smooth as possible for the volunteers of Foodsharing.
      </>
    ),
  },
  {
    title: 'Together, we are grounding Foodsharing',
    Svg: require('@site/static/img/1F91D.svg').default,
    description: (
      <>
        We are focusing on helping each other learning new things,
        and developing a solid base for foodsharing.
      </>
    ),
  },
  {
    title: 'Learning from old PHP code',
    Svg: require('@site/static/img/1F913.svg').default,
    description: (
      <>
        The code base is over 12 years old and but learning from such code could be fun.
      </>
    ),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} role="img" />
      </div>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
